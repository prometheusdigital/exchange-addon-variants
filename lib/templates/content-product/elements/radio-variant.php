<?php
/**
 * The default template part for the product variants
 * in the content-product template part's
 * radio variant element.
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
<?php do_action( 'it_exchange_content_product_before_variant_element' ); ?>
<div class="it-variant-options it-variant-radio-options">
	<?php do_action( 'it_exchange_content_product_begin_variant_element' ); ?>
	<div class="it-variant-title"><?php it_exchange( 'variant', 'title', 'format=text' ); ?></div>
	<ul class="it-variant-radios">
		<?php while( it_exchange( 'variant', 'values' ) ) : ?>
			<?php $checked = it_exchange( 'variant-value', 'get-is-default' ) ? 'checked' : ''; ?>
			<li><label><input type="radio" name="it-exchange-variant-<?php esc_attr_e( it_exchange( 'variant', 'get-id' ) ); ?>" class="it-variant-options" <?php esc_attr_e( $checked ); ?>/> <?php it_exchange( 'variant-value', 'title' ); ?></label></li>
		<?php endwhile; ?>
	</ul>
	<?php do_action( 'it_exchange_content_product_end_variant_element' ); ?>
</div>
<?php do_action( 'it_exchange_content_product_after_variant_element' ); ?>
