<?php
/**
 * The default template part for the product variants
 * in the content-product template part's
 * image variant element.
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
<div class="it-variant-options it-variant-image-options">
	<?php do_action( 'it_exchange_content_product_begin_variant_element' ); ?>
	<div class="it-variant-title"><?php it_exchange( 'variant', 'title', 'format=text' ); ?><?php _e( ': ', 'LION' ); ?>
		<span class="it-variant-title-selected"><?php it_exchange( 'variant', 'default', array( 'property' => 'title' ) ); ?></span>
	</div>
	<ul class="it-variant-image-list">
		<?php while( it_exchange( 'variant', 'values' ) ) : ?>
			<?php $selected_class = it_exchange( 'variant-value', 'get-is-default' ) ? 'selected' : ''; ?>
			<li class="it-variant-image <?php esc_attr_e( $selected_class ); ?>">
				<?php if ( false !== it_exchange( 'variant-value', 'get-image' ) ) { ?>
					<img width="55px" height="55px" title="<?php esc_attr_e( it_exchange( 'variant-value', 'get-title', array( 'format' => 'text' ) ) ); ?>" alt="<?php esc_attr_e( it_exchange( 'variant-value', 'get-title', array( 'format' => 'text' ) ) ); ?>" src="<?php it_exchange( 'variant-value', 'image' ); ?>" data-id="<?php esc_attr_e( it_exchange( 'variant-value', 'get-id' ) ); ?>"/>
				<?php } else { ?>
					<div class="it-variant-blank-image" title="<?php esc_attr_e( it_exchange( 'variant-value', 'get-title', array( 'format' => 'text' ) ) ); ?>" data-id="<?php esc_attr_e( it_exchange( 'variant-value', 'get-id' ) ); ?>"></div>
				<?php } ?>
			</li>
		<?php endwhile; ?>
	</ul>
	<?php it_exchange_add_hidden_variant_field(); // Do not remove this or variants will not work ?>
	<?php do_action( 'it_exchange_content_product_end_variant_element' ); ?>
</div>
<?php do_action( 'it_exchange_content_product_after_variant_element' ); ?>
