<?php
/**
 * Enqueues Variant scripts to the frontend products page
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_variants_addon_enqueue_scripts() {
	if ( ! it_exchange_is_page( 'product' ) )
		return;

	it_exchange_set_the_product_id();
	$product_id = it_exchange_get_the_product_id();
	if ( it_exchange_product_has_feature( $product_id, 'variants' ) ) {
		wp_enqueue_script( 'it-exchange-variants-addon-frontend-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/frontend-product.js' );
		wp_enqueue_style( 'it-exchange-variants-addon-frontend-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/frontend-product.css' );
		add_filter( 'wp_footer', 'it_exchange_variants_addon_print_product_variant_js' );
	}
}
add_action( 'wp_enqueue_scripts', 'it_exchange_variants_addon_enqueue_scripts' );

/**
 * Maybe define the ajax URL in JS
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_variants_addon_print_product_variant_js() {
	$product_id = it_exchange_get_the_product_id();
	?>
	<script type="text/javascript">
		if ( typeof ajaxurl === 'undefined' ) {
			// Added by exchange-addon-variants in lib/hooks.php if not already defined
			var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
		}
		var itExchangeVariantPricing = [];
		var itExchangeVariantImages  = [];
		<?php
		if ( $pricing = it_exchange_get_product_feature( $product_id, 'base-price', array( 'setting' => 'variants' ) ) ) {
			foreach( (array) $pricing as $combo => $price_data ) {
				?>itExchangeVariantPricing['<?php echo esc_js( $combo ); ?>'] = '<?php echo esc_js( it_exchange_format_price( $price_data['value'] ) ); ?>';
				<?php
			}
		}
		if ( $images = it_exchange_get_product_feature( $product_id, 'product-images', array( 'setting' => 'variants' ) ) ) {
			foreach( (array) $pricing as $combo => $price_data ) {
				?>itExchangeVariantImages.push('<?php echo esc_js( $combo ); ?>');<?php
			}
		}
		?>
	</script>
	<?php
}

/**
 * Enqueues Variant scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 * @return void
*/
function it_exchange_variants_addon_admin_wp_enqueue_scripts( $hook_suffix ) {
	global $post;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) )
			$post_id = (int) $_REQUEST['post'];
		elseif ( isset( $_REQUEST['post_ID'] ) )
			$post_id = (int) $_REQUEST['post_ID'];
		else
			$post_id = 0;

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}

	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type && ( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) ) {
		wp_enqueue_script( 'it-exchange-variants-addon-colorpicker', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/colorpicker/colorpicker.js' );
		wp_enqueue_script( 'it-exchange-variants-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', array( 'jquery', 'it-exchange-dialog', 'it-exchange-variants-addon-colorpicker' ) );

		// Backbone scripts
		$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/';
		$deps     = array( 'jquery', 'wp-backbone', 'underscore', 'jquery-ui-sortable', 'it-exchange-dialog' );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-models',  $url_base . 'models/variant-models.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-collections',  $url_base . 'collections/variant-collections.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-admin-views',  $url_base . 'views/variant-admin-views.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-admin-core',  $url_base . 'admin-variants.js', $deps );
		if( ! has_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' ) )
			add_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' );

		// Inventory integration
		if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post->ID ), 'inventory' ) ) {
			wp_enqueue_script( 'it-exchange-variants-addon-variant-inventory-models',  $url_base . 'models/variant-inventory-models.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-inventory-collections',  $url_base . 'collections/variant-inventory-collections.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-inventory-admin-views',  $url_base . 'views/variant-admin-inventory-views.js', $deps );
			if( ! has_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' ) )
				add_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' );
		}

		// Product Images integration
		if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post->ID ), 'product-images' ) ) {
			wp_enqueue_script( 'it-exchange-variants-addon-variant-images-models',  $url_base . 'models/variant-images-models.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-images-collections',  $url_base . 'collections/variant-images-collections.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-images-admin-views',  $url_base . 'views/variant-admin-images-views.js', $deps );
			if( ! has_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' ) )
				add_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' );
		}

		// Pricing integration
		if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post->ID ), 'base-price' ) ) {
			wp_enqueue_script( 'it-exchange-variants-addon-variant-pricing-models',  $url_base . 'models/variant-pricing-models.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-pricing-collections',  $url_base . 'collections/variant-pricing-collections.js', $deps );
			wp_enqueue_script( 'it-exchange-variants-addon-variant-pricing-admin-views',  $url_base . 'views/variant-admin-pricing-views.js', $deps );
			if( ! has_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' ) )
				add_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_variants_addon_admin_wp_enqueue_scripts' );

/**
 * Enqueues Variant styles to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 * @return void
*/
function it_exchange_variants_addon_admin_wp_enqueue_styles( $hook_suffix ) {
	global $post;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) )
			$post_id = (int) $_REQUEST['post'];
		elseif ( isset( $_REQUEST['post_ID'] ) )
			$post_id = (int) $_REQUEST['post_ID'];
		else
			$post_id = 0;

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}

	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_style( 'it-exchange-variants-addon-colorpicker', ITUtility::get_url_from_file( dirname( __FILE__) ) . '/js/colorpicker/colorpicker.css' );
		wp_enqueue_style( 'it-exchange-variants-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/add-edit-product.css' );
	}
}
add_action( 'admin_print_styles', 'it_exchange_variants_addon_admin_wp_enqueue_styles' );

/**
 * Checks to see if the presets exist
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_variants_addon_setup_preset_variants() {
	it_exchange_variants_addon_create_inital_presets();
}
add_action( 'admin_init', 'it_exchange_variants_addon_setup_preset_variants' );

/**
 * Prints the hash id for a combo of variants via ajax
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_variants_addon_ajax_get_selected_variants_id_hash() {
	if ( empty( $_POST['it_exchange_selected_variants'] ) )
		return false;

	$variants_to_hash = array();
	foreach( (array) $_POST['it_exchange_selected_variants'] as $id ) {
		if ( $variant = it_exchange_variants_addon_get_variant( $id ) )
			$variants_to_hash[empty( $variant->post_parent ) ? $id : $variant->post_parent] = $id;
	}
	die( empty( $variants_to_hash ) ? false : it_exchange_variants_addon_get_selected_variants_id_hash( $variants_to_hash ) );
}
add_action( 'wp_ajax_it_exchange_variants_get_selected_id_hash', 'it_exchange_variants_addon_ajax_get_selected_variants_id_hash' );
add_action( 'wp_ajax_nopriv_it_exchange_variants_get_selected_id_hash', 'it_exchange_variants_addon_ajax_get_selected_variants_id_hash' );

function it_exchange_variants_addon_load_backbone_admin_templates() {
	include( dirname( __FILE__ ) . '/js/templates/admin.php' );

	$post_id = empty( $GLOBALS['post']->ID ) ? 0 : $GLOBALS['post']->ID;

	// Inventory
	if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post_id ), 'inventory' ) )
		include( dirname( __FILE__ ) . '/js/templates/admin-product-inventory-variants.php' );

	// Product Images
	if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post_id ), 'product-images' ) )
		include( dirname( __FILE__ ) . '/js/templates/admin-product-images-variants.php' );

	// Pricing Variants
	if ( it_exchange_product_type_supports_feature( it_exchange_get_product_type( $post_id ), 'base-price' ) )
		include( dirname( __FILE__ ) . '/js/templates/admin-product-pricing-variants.php' );
}

function it_exchange_variants_json_api() {

	$endpoint       = empty( $_REQUEST['endpoint'] ) ? false : $_REQUEST['endpoint'];
	$product_id     = empty( $_REQUEST['product-id'] ) ? false : $_REQUEST['product-id'];
	$variant_id     = empty( $_REQUEST['product-variant'] ) ? false : $_REQUEST['product-variant'];
	$preset_id      = empty( $_REQUEST['preset-id'] ) ? false : $_REQUEST['preset-id'];
	$parent_id      = empty( $_REQUEST['parent-id'] ) ? false : $_REQUEST['parent-id'];
	$ui_type        = empty( $_REQUEST['ui-type'] ) ? false : $_REQUEST['ui-type'];
	$variants_array = empty( $_REQUEST['variants-array'] ) ? false : (array) $_REQUEST['variants-array'];
	$include_currency_data = empty( $_REQUEST['include-currency-data'] ) ? false : true;

	if ( empty( $endpoint ) )
		return false;

	if ( 'product-variants' == $endpoint ) {
		if ( ! empty( $product_id ) ) {
			$variants  = (array) it_exchange_get_variants_for_product( $product_id );
			$response = array();
			foreach( $variants as $variant ) {
				if ( empty( $variant->ID ) )
					continue;
				$response_variant = new stdClass();
				$response_variant->id            = $variant->ID;
				$response_variant->title         = $variant->post_title;
				$response_variant->order         = $variant->menu_order;
				$response_variant->uiType        = $variant->ui_type;
				$response_variant->presetSlug    = $variant->preset_slug;
				$response_variant->default       = $variant->default;
				$response_variant->valuesPreview = '';

				$response[] = $response_variant;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'variant-values' == $endpoint ) {
		if ( ! empty( $variant_id ) ) {
			$parent   = it_exchange_variants_addon_get_variant( $variant_id );
			$variants = (array) it_exchange_get_values_for_variant( $variant_id );
			$response = array();
			foreach( $variants as $variant ) {
				if ( empty( $variant->ID ) )
					continue;
				$response_variant = new stdClass();
				$response_variant->id            = $variant->ID;
				$response_variant->parentId     = $variant->post_parent;
				$response_variant->title         = $variant->post_title;
				$response_variant->order         = $variant->menu_order;
				$response_variant->uiType        = empty( $parent->ui_type ) ? false : $parent->ui_type;
				$response_variant->color         = empty( $variant->color ) ? false : $variant->color;
				$response_variant->imageUrl      = empty( $variant->image ) ? '' : $variant->image;
				$response_variant->isDefault     = ( ! empty( $parent->default ) && $parent->default == $variant->ID ) ? 'checked' : '';
				$response_variant->presetSlug    = empty( $parent->preset_slug ) ? false : $parent->preset_slug;

				$response[] = $response_variant;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'variant-values-from-preset' == $endpoint ) {
		if ( ! empty( $preset_id ) ) {
			$preset = it_exchange_variants_addon_get_preset( $preset_id );
			$values = empty( $preset->values ) ? array() : $preset->values;

			$response = array();
			foreach( $values as $value ) {
				$response_value = new stdClass();
				$response_value->id            = uniqid(rand());
				$response_value->parentId      = $parent_id;
				$response_value->title         = $value['title'];
				$response_value->order         = empty( $value['order'] ) ? 0 : $value['order'];
				$response_value->color         = empty( $value['color'] ) ? false : $value['color'];
				$response_value->imageUrl      = empty( $value['image'] ) ? '' : $value['image'];
				$response_value->uiType        = empty( $preset->ui_type ) ? false : $preset->ui_type;
				$response_value->isDefault     = empty( $preset->default ) ? '' : 'checked';
				$response_value->presetSlug    = empty( $preset->slug ) ? false : $preset->slug;

				$response[] = $response_value;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'variant-value-from-ui-type' == $endpoint ) {
		if ( ! empty( $parent_id ) && ! empty( $ui_type ) ) {
			if ( $presets = it_exchange_variants_addon_get_presets( array( 'core_only' => true ) ) ) {

				foreach( $presets as $preset ) {
					if ( ! $preset->is_template || empty( $preset->ui_type ) || $ui_type != $preset->ui_type || empty( $preset->values[0] ) )
						continue;

					$value = $preset->values[0];

					$response = new stdClass();
					$response->id            = uniqid(rand());
					$response->parentId      = (int) $parent_id;
					$response->title         = $preset->title;
					$response->order         = empty( $value['order'] ) ? 0 : $value['order'];
					$response->color         = empty( $value['color'] ) ? false : $value['color'];
					$response->imageUrl      = empty( $value['image'] ) ? '' : $value['image'];
					$response->uiType        = empty( $preset->ui_type ) ? false : $preset->ui_type;
					$response->isDefault     = '';
					$response->presetSlug    = empty( $preset->slug ) ? false : $preset->slug;

					// We only want one so die here
					die( json_encode( $response ) );
				}
			}
		}
	} else if ( 'core-presets' == $endpoint ) {
		if ( $presets = it_exchange_variants_addon_get_presets( array( 'core_only' => true ) ) ) {
			$reponse = array();
			foreach( $presets as $preset ) {
				if ( ! $preset->is_template )
					continue;
				$core_preset             = new stdClass();
				$core_preset->id         = $preset->ID;
				$core_preset->slug       = $preset->slug;
				$core_preset->title      = $preset->title;
				$core_preset->values     = $preset->values;
				$core_preset->order      = empty( $preset->menu_order ) ? 0 : $preset->menu_order;
				$core_preset->uiType     = empty( $preset->ui_type ) ? '' : $preset->ui_type;
				$core_preset->imageAlt   = $preset->title;
				$core_preset->imageThumb = ( ! empty( $preset->ui_type ) && is_file( dirname( __FILE__ ) . '/images/presets/' . $preset->ui_type . '.png' ) )
					? ITUtility::get_url_from_file( dirname( __FILE__ ) . '/images/presets/' . $preset->ui_type . '.png' )
					: '';

				$response[] = $core_preset;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'saved-presets' == $endpoint ) {
		if ( $presets = it_exchange_variants_addon_get_presets() ) {
			$reponse = array();
			foreach( $presets as $preset ) {
				if ( $preset->is_template )
					continue;
				$core_preset             = new stdClass();
				$core_preset->id         = $preset->ID;
				$core_preset->slug       = $preset->slug;
				$core_preset->title      = $preset->title;
				$core_preset->order      = empty( $preset->menu_order ) ? 0 : $preset->menu_order;
				$core_preset->uiType     = empty( $preset->ui_type ) ? '' : $preset->ui_type;
				$core_preset->values     = $preset->values;
				$core_preset->imageAlt   = $preset->title;
				$core_preset->imageThumb = ( ! empty( $preset->ui_type ) && is_file( dirname( __FILE__ ) . '/images/presets/' . $preset->ui_type . '.png' ) )
					? ITUtility::get_url_from_file( dirname( __FILE__ ) . '/images/presets/' . $preset->ui_type . '.png' )
					: '';

				$response[] = $core_preset;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'available-inventory-combos' == $endpoint ) {
		if ( $raw_combos = it_exchange_variants_addon_get_all_variant_combos_for_product( $product_id, false ) ) {
			$response = array();
			$product_variants = it_exchange_get_product_feature( $product_id, 'variants' );
			$variants_version = empty( $product_variants['variants_version'] ) ? false : $product_variants['variants_version'];

			// Grab the value from the inventory postmeta if it exists
			$inventory_post_meta = it_exchange_get_product_feature( $product_id, 'inventory', array( 'setting' => 'variants' ) );

			foreach( $raw_combos as $raw_combo ) {
				$combo_attributes = it_exchange_get_variant_combo_attributes( $raw_combo );
				foreach( $combo_attributes['combo'] as $key => $variant_id ) {
					$parent_title = get_the_title( $key );
					$child_title  = get_the_title( $variant_id );
					unset( $combo_attributes['combo'][$key] );
					$combo_attributes['combo'][$parent_title] = $child_title;
				}

				$combo = new stdClass();
				$combo->ID       = $combo_attributes['hash'];
				$combo->id       = $combo_attributes['hash'];
				$combo->hash     = $combo_attributes['hash'];
				$combo->variants = (array) $combo_attributes['combo'];
				$combo->title    = empty( $combo_attributes['title'] ) ? '' : $combo_attributes['title'];
				$combo->value    = empty( $inventory_post_meta[$combo->hash] ) ? 0 : $inventory_post_meta[$combo->hash]['value'];
				$combo->version  = $variants_version;

				$response[] = $combo;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'missing-inventory-combos' == $endpoint ) {
		$controller = it_exchange_variants_addon_get_product_feature_controller( $product_id, 'inventory', array( 'setting' => 'variants' ) );
		if ( $controller->variants_were_updated() && ! empty( $controller->post_meta ) ) {
			$response = array();
			foreach( $controller->post_meta as $hash => $missing ) {

				$combo = new stdClass();
				$combo->ID       = $hash;
				$combo->id       = $hash;
				$combo->hash     = $hash;
				$combo->variants = empty( $missing['variants_title_array'] ) ? array() : $missing['variants_title_array'];
				$combo->title    = empty( $missing['combos_title'] ) ? '' : $missing['combos_title'];
				$combo->value    = empty( $missing['value'] ) ? 0 : $missing['value'];
				$combo->version  = $controller->product_feature_variants_version;

				$response[] = $combo;
			}
				die( json_encode( $response) );
		}
	} else if ( 'product-variant-hierarchy' == $endpoint ) {
		if ( ! empty( $product_id ) ) {
			$variants  = (array) it_exchange_get_variants_for_product( $product_id );
			$variants_version = it_exchange_get_product_feature( $product_id, 'variants' );
			$variants_version = empty( $variants_version['variants_version'] ) ? false : $variants_version['variants_version'];
			$response = array();
			foreach( $variants as $variant ) {
				if ( empty( $variant->ID ) )
					continue;
				$response_variant = new stdClass();
				$response_variant->id            = $variant->ID;
				$response_variant->title         = $variant->post_title;
				$response_variant->version       = $variants_version;
				$response_variant->values        = array();

				if ( ! empty( $variant->values ) ) {
					foreach( $variant->values as $value ) {
						$value_object        = new stdClass();
						$value_object->id    = $value->ID;
						$value_object->title = $value->post_title;
						$response_variant->values[] = $value_object;
					}
				}

				$response[] = $response_variant;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'get-atts-from-raw-combo' == $endpoint ) {
		if ( ! empty( $variants_array ) ) {
			$result = new stdClass();
			$result->hash  = '';
			$result->title = '';
			$result->combo = array();
			$settings                 = it_exchange_get_option( 'settings_general' );
			$currency                 = it_exchange_get_currency_symbol( $settings['default-currency'] );
			if ( $response = it_exchange_get_variant_combo_attributes( $variants_array ) ) {
				$result->hash       = empty( $response['hash'] ) ? $result->hash : $response['hash'];
				$result->title      = empty( $response['title'] ) ? $result->title : $response['title'];
				$result->combo      = empty( $response['combo'] ) ? $result->combo : $response['combo'];
				$result->allParents = true;

				foreach( $result->combo as $parent => $child ) {
					if ( $parent != $child ) {
						$result->allParents = false;
						break;
					}
				}
				if ( ! empty( $include_currency_data ) ) {
					$result->symbol         = $currency;
					$result->symbolPosition = $settings['currency-symbol-position'];
					$result->thousandsSep   = $settings['currency-thousands-separator'];
					$result->decimalsSep    = $settings['currency-decimals-separator'];
				}
			}
			die( json_encode($result) );
		}
	} else if ( 'get-hash-from-raw-combo' == $endpoint ) {
		if ( ! empty( $variants_array ) ) {
			$variants_to_hash = array();
			foreach( $variants_array as $key => $variant_id ) {
				if ( 'it_exchange_variant' != get_post_type( $variant_id ) )
					continue;
				$parent = wp_get_post_parent_id( $variant_id );
				$parent = empty( $parent ) ? $variant_id : $parent;
				$variants_to_hash[$parent] = $variant_id;
			}
			if ( ! empty( $variants_to_hash ) )
				die( it_exchange_variants_addon_get_selected_variants_id_hash( $variants_to_hash ) );
		}
	} else if ( 'existing-images-combos' == $endpoint ) {
		$response                = array();
		$product_variants        = it_exchange_get_product_feature( $product_id, 'variants' );
		$variants_version        = empty( $product_variants['variants_version'] ) ? false : $product_variants['variants_version'];
		$images_variants_version = it_exchange_get_product_feature( $product_id, 'product-images', array( 'setting' => 'variants-version' ) );

		// Grab the value from the product images postmeta if it exists
		if ( $images_post_meta = it_exchange_get_product_feature( $product_id, 'product-images', array( 'setting' => 'variants' ) ) ) {

			// Loop through post meta data to build the correct format for the JSON request
			foreach( $images_post_meta as $hash => $data ) {
				$combo = new stdClass();
				$combo->ID       = $hash;
				$combo->id       = $hash;
				$combo->hash     = $hash;
				$combo->variants = (array) $data['combos_to_hash'];
				$combo->title    = empty( $data['combos_title'] ) ? '' : $data['combos_title'];
				$combo->value    = empty( $data['value'] ) ? array() : array_values( $data['value'] );
				$combo->version  = $variants_version;
				$combo->thumbURL = '';
				$combo->featuredImage = false;
				$combo->productImages = array();
				$combo->invalidCombo = false;

				// Populate Featured Image and standard images
				foreach( $combo->value as $key => $image_id ) {
					$image = new stdClass();
					$image->imageID  = $image_id;
					$image->int      = $key;
					$image->cssID    = uniqid();
					$image->featured = (0 === $image->int);
					$image->thumbURL = wp_get_attachment_thumb_url( $image_id );
					$image->largeURL = wp_get_attachment_url( $image_id );

					if ( $image->featured ) {
						$combo->featuredImage = $image;
						$combo->thumbURL = $image->thumbURL;
					} else {
						$combo->productImages[$key] = $image;
					}
				}

				// Check to make sure this variant combo is still legitimate
				if ( $variants_version != $images_variants_version )
					$combo->invalidCombo = true;

				$response[] = $combo;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'existing-pricing-combos' == $endpoint ) {
		$response                 = array();
		$product_variants         = it_exchange_get_product_feature( $product_id, 'variants' );
		$variants_version         = empty( $product_variants['variants_version'] ) ? false : $product_variants['variants_version'];
		$pricing_variants_version = it_exchange_get_product_feature( $product_id, 'base-price', array( 'setting' => 'variants-version' ) );
		$settings                 = it_exchange_get_option( 'settings_general' );
		$currency                 = it_exchange_get_currency_symbol( $settings['default-currency'] );

		// Grab the value from the product pricing postmeta if it exists
		if ( $pricing_post_meta = it_exchange_get_product_feature( $product_id, 'base-price', array( 'setting' => 'variants' ) ) ) {

			// Loop through post meta data to build the correct format for the JSON request
			foreach( (array) $pricing_post_meta as $hash => $data ) {
				$combo = new stdClass();
				$combo->ID             = $hash;
				$combo->id             = $hash;
				$combo->hash           = $hash;
				$combo->variants       = (array) $data['combos_to_hash'];
				$combo->title          = empty( $data['combos_title'] ) ? '' : $data['combos_title'];
				$combo->value          = empty( $data['value'] ) ? false : it_exchange_format_price( $data['value'] );
				$combo->version        = $variants_version;
				$combo->invalidCombo   = false;
				$combo->symbol         = $currency;
				$combo->symbolPosition = $settings['currency-symbol-position'];
				$combo->thousandsSep   = $settings['currency-thousands-separator'];
				$combo->decimalsSep    = $settings['currency-decimals-separator'];

				// Check to make sure this variant combo is still legitimate
				if ( $variants_version != $pricing_variants_version )
					$combo->invalidCombo = true;

				$response[] = $combo;
			}
			die( json_encode( $response ) );
		}
	}
	return false;
}
add_action( 'wp_ajax_it-exchange-variants-json-api', 'it_exchange_variants_json_api' );

/**
 * Extend the product theme api to include new tags
 *
*/
function it_exchange_addon_variants_extend_product_theme_api( $result, $class, $tag ){
	if ( is_admin() )
		return;
	if ( 'IT_Theme_API_Product'== $class && 'variants' == $tag ) {
		include_once( dirname( dirname( __FILE__ ) ) . '/api/theme/variant-product.php' );
		$IT_Theme_API_Product_Extension_For_Variants = new IT_Theme_API_Product_Extension_For_Variants();
		$result = array( $IT_Theme_API_Product_Extension_For_Variants, 'variants' );
	}
	return $result;
}
add_filter( 'it_exchange_theme_api_get_extended_tag_functions', 'it_exchange_addon_variants_extend_product_theme_api', 10, 3 );
