<?php
/**
 * Tarjeta de producto reutilizable (bloque de destacados, carruseles, etc.).
 *
 * @var array{product:WC_Product,show_cart?:bool} $args
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}
$show_cart = ! empty( $args['show_cart'] );
$link      = $product->get_permalink();
$image_id  = $product->get_image_id();
$badges    = fandeco_product_badges( $product );
$options   = fandeco_variation_options( $product );
$max_chips = 4;
?>
<article class="fd-product-card" data-fd-reveal>
	<a class="fd-product-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $image_id ) {
			echo wp_get_attachment_image( $image_id, 'fandeco-card', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 70vw, (max-width: 1023px) 40vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo wc_placeholder_img( 'fandeco-card' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</a>
	<?php if ( $badges ) : ?>
		<ul class="fd-product-card__badges">
			<?php foreach ( $badges as $badge ) : ?>
				<li><?php echo esc_html( $badge ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<div class="fd-product-card__body">
		<h3 class="fd-product-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<div class="fd-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php if ( $options ) : ?>
			<ul class="fd-product-card__options" aria-label="<?php esc_attr_e( 'Variaciones disponibles', 'fan-deco-studio' ); ?>">
				<?php foreach ( array_slice( $options, 0, $max_chips ) as $option ) : ?>
					<li><?php echo esc_html( $option ); ?></li>
				<?php endforeach; ?>
				<?php if ( count( $options ) > $max_chips ) : ?>
					<li>+<?php echo (int) ( count( $options ) - $max_chips ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
		<?php if ( $show_cart ) : ?>
			<?php
			$can_ajax = 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) && $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock();
			printf(
				'<a href="%1$s" class="fd-btn fd-btn--outline fd-btn--sm %2$s" data-product_id="%3$s" data-product_sku="%4$s" aria-label="%5$s" rel="nofollow">%6$s</a>',
				esc_url( $product->add_to_cart_url() ),
				$can_ajax ? 'add_to_cart_button ajax_add_to_cart' : '',
				esc_attr( (string) $product->get_id() ),
				esc_attr( $product->get_sku() ),
				esc_attr( wp_strip_all_tags( $product->add_to_cart_description() ) ),
				esc_html( $product->add_to_cart_text() )
			);
			?>
		<?php endif; ?>
	</div>
</article>
