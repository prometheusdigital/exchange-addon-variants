<?php
/**
 * This generates a series of variant form field for the variant metabox.
 * @since 1.0.0
 * @package IT_Exchange_Variants_Addon
*/
class IT_Exchange_Variants_Addon_Form_Field {
	
	var $init_type              = false;
	var $id                     = false;
	
	var $object                 = false;
	var $object_values          = array();
	var $variant_id             = false;
	var $variant_title          = false;
	var $variant_values_preview = '';
	var $div                    = '';

	function IT_Exchange_Variants_Addon_Form_Field( $type, $id=false ) {
		if ( ! in_array( $type, array( 'template', 'saved', 'existing' ) ) )
			return;

		$this->init_type = $type;
		$this->id        = $id;

		$init_func = 'init_' . $type;
		$this->$init_func();
		$this->start_div();
		$this->add_variant_title();
		$this->add_variant_values();
	}

	function init_template() {

		// Load the template data
		$this->object = it_exchange_variants_addon_get_preset( $this->id );
		$this->object_values = $this->object->get_property( 'values' );
		
		// Set a temp ID for this new div
		$this->id            = uniqid();
		$this->variant_title = sprintf( __( 'New %s Variant Title', 'LION' ), $this->object->get_property( 'title' ) );
	}

	function init_saved() {

		// Load the template data
		$this->object        = it_exchange_variants_addon_get_preset( $this->id );
		$this->object_values = $this->object->get_property( 'values' );

		// Set a temp ID for this new div
		$this->id            = uniqid();
		$this->variant_title = sprintf( __( 'New %s Variant Title', 'LION' ), $this->object->get_property( 'title' ) );
	}

	function start_div() {
		$this->div .= '<div class="it-exchange-existing-variant" data-variant-id="' . esc_attr( $this->variant_id ) . '" data-variant-open="true">';
	}

	function add_variant_title() {
		$title = '
		<div class="variant-title">
			<span class="variant-title-move"></span>
			<span class="variant-title-text variant-text-placeholder">' . $this->variant_title . '</span>
			<input type="text" name="variant_title_text" value="' . esc_attr( $this->variant_title ) . '" class="variant-text-input hidden">
			<span class="variant-title-values-preview">
				' . $this->variant_values_preview . '
			</span>
			<span class="variant-title-delete it-exchange-remove-item">
				&times;
			</span>
		</div>
		';

		$this->div .= $title;
	}

	function add_variant_values() {
		if ( ! empty( $this->object->post_parent ) )
			return;

		$values = '
		<div class="variant-values">
			<div class="edit-variant">
				<span class="label">' . __( 'Values', 'LION' ) . ' ' . it_exchange_admin_tooltip( 'tooltip goes here', false ) .'</span>
				<ul class="variant-values-list">
					<li class="new-variant-value clearfix hidden" data-variant-value-id="" data-variant-value-parent="' . esc_attr( $this->id ) . '">
						<div class="variant-value-reorder" data-variant-value-order=""></div>
						<div class="variant-value-info">
							<input type="radio" class="variant-radio-option" name="default-for-variant-' . esc_attr( $this->id ) . '"/>
							<span class="variant-value-name variant-text-placeholder">' . __( 'New Value', 'LION' ) . '</span>
							<input type="text" name="variant-value-name[]" value="' . __( 'New Value', 'LION' ) . '" class="variant-text-input hidden">
							' . $this->get_variant_value_visual() . '
						</div>
						<div class="variant-value-delete">
							<a href class="it-exchange-remove-item">&times;</a>
						</div>
					</li>';

				$int = 0;

				foreach( $this->object_values as $value ) {
					$int++;
					$value               = $this->get_child_variant( $value );
					$this->current_value = $value;
					$value_id            = $value->get_property( 'ID' );
					$value_title         = $value->get_property( 'title' );

					$values .= '
					<li class="clearfix" data-variant-value-id="' . esc_attr( $value_id ) . '" data-variant-value-parent="' . esc_attr( $this->id ) . '">
						<div class="variant-value-reorder" data-variant-value-order="' . esc_attr( $int ) . '"></div>
						<div class="variant-value-info">
							<input type="radio" class="variant-radio-option" name="default-for-variant-' . esc_attr( $value_id ) . '" />
							<span class="variant-value-name variant-text-placeholder">' . $value_title . '</span>
							<input type="text" name="variant-value-name[101]" value="' . esc_attr( $value_title ) . '" class="variant-text-input hidden" />
							' . $this->get_variant_value_visual() . '
						</div>
						<div class="variant-value-delete">
							<a href class="it-exchange-remove-item">&times;</a>
						</div>
					</li>
					';
				}

				$values .= '
				</ul>
				<div class="add-variant-value">
					<input type="button" class="button add-variant-value-button" value="Add Value" />
				</div>
			</div>
		</div>
		';
		$this->div .= $values;
	}

	function get_child_variant( $value ) {
		switch( $this->init_type ) {
			case 'template' :
				return is_array( $value ) ? it_exchange_variants_addon_get_saved_preset_value( $value ) : it_exchange_variants_addon_get_preset( $value );	
				break;
			case 'saved' :
				return it_exchange_variants_addon_get_saved_preset_value( $value );
				break;
			case 'existing' :
				die( __FILE__ . ' | ' . __LINE__ );
		}
	}

	function get_variant_value_visual() {
		$html = '';
		switch( $this->init_type ) {
			case 'template' :
				$slug = $this->object->get_property( 'slug' );
				if ( 'template-image' == $slug ) {
					$html = '
					<a class="variant-value-image variant-value-has-image">
						<span class="variant-value-image-placeholder"></span>
					</a>
					';
				} elseif ( 'template-hex' == $slug ) {
					$html = '
					<div class="variant-value-hex">
						<input type="text" value="#F1FFDE" name="it-exchange-variant-value-color[]" class="it-exchange-variants-colorpicker" />
					</div>
					';
				}
				break;
			case 'saved' :
				if ( ! empty( $this->object->post_parent ) ) {
					$parent = it_exchange_variant_addon_get_preset( $this->object->post_parent );
					$ui_type = $parent->get_property->ui_type;
				} else {
					$ui_type = $this->object->get_property( 'ui_type' );
				}
				if ( 'image' == $ui_type ) {
					$html = '
					<a class="variant-value-image variant-value-has-image">
						<span class="variant-value-image-placeholder"></span>
					</a>
					';
				} elseif ( 'color' == $ui_type ) {
					$default = empty( $this->current_value->color ) ? $this->object->get_property( 'default' ) : $this->current_value->get_property( 'color' );
					$html = '
					<div class="variant-value-hex">
						<input type="text" value="' . esc_attr( $default ) . '" name="it-exchange-variant-value-color[]" class="it-exchange-variants-colorpicker" />
					</div>
					';
				}
				break;
		}
		return $html;
	}
}