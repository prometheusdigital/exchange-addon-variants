<?php
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
	
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_script( 'it-exchange-variants-addon-colorpicker', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/colorpicker/colorpicker.js' );
//		wp_enqueue_script( 'it-exchange-variants-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', array( 'jquery', 'jquery-ui-sortable', 'it-exchange-dialog', 'it-exchange-variants-addon-colorpicker' ) );

		// Backbone scripts
		$url_base = ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/';
		$deps     = array( 'jquery', 'wp-backbone', 'underscore' );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-models',  $url_base . 'models/variant-models.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-collections',  $url_base . 'collections/variant-collections.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-core-admin-view',  $url_base . 'views/core-admin-view.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-add-edit-variant-view',  $url_base . 'views/add-edit-variant-view.js', $deps );
		wp_enqueue_script( 'it-exchange-variants-addon-variant-admin-core',  $url_base . 'admin-variants.js', $deps );
		add_action( 'admin_footer', 'it_exchange_variants_addon_load_backbone_admin_templates' );
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
}

function it_exchange_variants_json_api() {

	$endpoint   = empty( $_REQUEST['endpoint'] ) ? false : $_REQUEST['endpoint'];
	$product_id = empty( $_REQUEST['product-id'] ) ? false : $_REQUEST['product-id'];
	$variant_id = empty( $_REQUEST['product-variant'] ) ? false : $_REQUEST['product-variant'];

	if ( empty( $endpoint ) )
		return false;

	if ( 'product-variants' == $endpoint ) {
		if ( ! empty( $product_id ) ) {
			$variants  = (array) it_exchange_get_variants_for_product( $product_id );
			$response = array();
			foreach( $variants as $variant ) {
				$response_variant = new stdClass();
				$response_variant->id            = $variant->ID;
				$response_variant->title         = $variant->post_title;
				$response_variant->order         = $variant->menu_order;
				$response_variant->uiType        = $variant->ui_type;
				$response_variant->presetSlug    = $variant->preset_slug;
				$response_variant->valuesPreview = 'preview here';

				$response[] = $response_variant;
			}
			die( json_encode( $response ) );
		}
	} else if ( 'product-variant-values' == $endpoint ) {
		if ( ! empty( $variant_id ) ) {
			$variants  = (array) it_exchange_get_values_for_variant( $variant_id );
			$response = array();
			foreach( $variants as $variant ) {
				$response_variant = new stdClass();
				$response_variant->id            = $variant->ID;
				$response_variant->title         = $variant->post_title;
				$response_variant->order         = $variant->menu_order;
				$response_variant->uiType        = $variant->ui_type;
				$response_variant->presetSlug    = $variant->preset_slug;
				$response_variant->valuesPreview = 'preview here';

				$response[] = $response_variant;
			}
			die( json_encode( $response ) );
		}
	}
	return false;
}
add_action( 'wp_ajax_it-exchange-variants-json-api', 'it_exchange_variants_json_api' );
