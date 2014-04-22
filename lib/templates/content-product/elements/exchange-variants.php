<?php
/**
 * The default template part for the product variants
 * in the content-product template part's
 * product-info loop.
 *
 * @since 1.0.0
 * @version 1.0.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-product/elements
 * directory located in your theme.
*/
?>
<?php //if ( it_exchange( 'product', 'has-base-price' ) ) : ?>
	<?php do_action( 'it_exchange_content_product_before_variants_element' ); ?>
	<div class="it-exchange-product-variants">
		<?php do_action( 'it_exchange_content_product_begin_variants_element' ); ?>
		<!-- Start HARDCODED DIVS for Ty -->
		<div class="variant-options variant-select-options">
			<h3 class="variant-title">Dropdown Variants</h3>
			<select class="variant-options">
				<option>Select Option One</option>
				<option selected>Select Option Two</option>
				<option>Select Option Three</option>
			</select>
		</div>

		<div class="variant-options variant-radio-options">
			<h3 class="variant-title">Radio Variants</h3>
			<ul class="variant-radios">
				<li><label><input type="radio" class="variant-options" checked/> Radio Option One</label></li>
				<li><label><input type="radio" class="variant-options" /> Radio Option Two</label></li>
				<li><label><input type="radio" class="variant-options" /> Radio Option Three</label></li>
			</ul>
		</div>

		<div class="variant-options variant-image-options">
			<h3 class="variant-title">Image Variants</h3>
			<div class-"variant-image">
				<img src="http://placehold.it/75x75" />
			</div>
			<div class-"variant-image selected">
				<img src="http://placehold.it/75x75" />
			</div>
			<div class-"variant-image">
				<img src="http://placehold.it/75x75" />
			</div>
		</div>

		<div class="variant-options variant-hex-options">
			<h3 class="variant-title">Color Variants</h3>
			<div class-"variant-color">
				<div class="variant-color-inner" style="height:50px;width:50px;background:#F1FFDE"></div>
			</div>
			<div class-"variant-color selected">
				<div class="variant-color-inner" style="height:50px;width:50px;background:#0082CA"></div>
			</div>
			<div class-"variant-color">
				<div class="variant-color-inner" style="height:50px;width:50px;background:#334940"></div>
			</div>
		</div>
		<!-- END HARDCODED DIVS for Ty -->
		<?php do_action( 'it_exchange_content_product_end_variants_element' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_product_after_variants_element' ); ?>
<?php //endif; ?>
