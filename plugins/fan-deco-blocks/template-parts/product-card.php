<?php
/**
 * Tarjeta de producto del tema: la pieza vive dentro de una ventana con esquinas suaves y un resplandor.
 * Se usa en los listados de WooCommerce (vía ganchos) y en el bloque de productos destacados.
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
$options   = fandeco_variation_swatch_data( $product );
$max_dots  = 5;

// Segunda foto para alternar al pasar el mouse: la foto "encendida" (si hay) o la primera de la galería.
$alt_id = 0;
if ( $image_id ) {
	foreach ( $product->get_gallery_image_ids() as $gid ) {
		if ( (int) $gid !== (int) $image_id ) {
			$alt_id = (int) $gid;
			break;
		}
	}
}
$alt_id  = (int) apply_filters( 'fandeco_card_hover_image_id', $alt_id, $product );
$alt_id  = ( $alt_id && (int) $alt_id !== (int) $image_id && wp_attachment_is_image( $alt_id ) ) ? $alt_id : 0;
$alt_lit = (bool) apply_filters( 'fandeco_card_hover_is_lit', false, $product, $alt_id );
?>
<article class="fd-product-card">
	<div class="fd-product-card__window<?php echo $alt_id ? ' has-alt' : ''; ?>">
		<a class="fd-product-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'fandeco-card', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 50vw, (max-width: 1023px) 33vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				echo wc_placeholder_img( 'fandeco-card' ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $alt_id ) {
				echo wp_get_attachment_image( $alt_id, 'fandeco-card', false, array( 'class' => 'fd-product-card__alt', 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '', 'sizes' => '(max-width: 767px) 50vw, (max-width: 1023px) 33vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</a>
		<?php if ( $alt_id ) : ?>
			<div class="fd-product-card__photos<?php echo $alt_lit ? ' is-lit' : ''; ?>" role="group" aria-label="<?php esc_attr_e( 'Fotos del producto', 'fan-deco-studio' ); ?>">
				<button type="button" class="fd-photo-dot" data-fd-photo="0" aria-label="<?php esc_attr_e( 'Ver la primera foto', 'fan-deco-studio' ); ?>"></button>
				<button type="button" class="fd-photo-dot" data-fd-photo="1" aria-label="<?php echo esc_attr( $alt_lit ? __( 'Ver el producto encendido', 'fan-deco-studio' ) : __( 'Ver la segunda foto', 'fan-deco-studio' ) ); ?>"></button>
			</div>
		<?php endif; ?>
		<?php if ( $badges ) : ?>
			<ul class="fd-product-card__badges">
				<?php foreach ( $badges as $badge ) : ?>
					<li><?php echo esc_html( $badge ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php
		$fd_dots = array_values( array_filter( $options, static fn( $o ) => ! empty( $o['color'] ) ) );
		if ( $fd_dots ) : // solo variaciones de color; se muestran como puntos dentro de la imagen
			?>
			<ul class="fd-product-card__options" aria-label="<?php esc_attr_e( 'Colores disponibles', 'fan-deco-studio' ); ?>">
				<?php foreach ( array_slice( $fd_dots, 0, $max_dots ) as $option ) : ?>
					<li class="fd-dot" style="--fd-swatch:<?php echo esc_attr( $option['color'] ); ?>" title="<?php echo esc_attr( $option['name'] ); ?>"><span class="screen-reader-text"><?php echo esc_html( $option['name'] ); ?></span></li>
				<?php endforeach; ?>
				<?php if ( count( $fd_dots ) > $max_dots ) : ?>
					<li class="fd-dots-more">+<?php echo (int) ( count( $fd_dots ) - $max_dots ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
		<?php if ( empty( $args['no_quickview'] ) ) : ?>
		<button type="button" class="fd-quickview" data-fd-quickview="<?php echo (int) $product->get_id(); ?>" data-href="<?php echo esc_url( $link ); ?>" aria-haspopup="dialog" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: nombre del producto */ __( 'Vista rápida: %s', 'fan-deco-studio' ), $product->get_name() ) ); ?>"><?php echo fandeco_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Vista rápida', 'fan-deco-studio' ); ?></span></button>
		<?php endif; ?>
	</div>
		<?php if ( $show_cart ) : ?>
			<div class="fd-product-card__add"><?php echo fandeco_loop_add_to_cart_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput -- salida oficial de WooCommerce. ?></div>
		<?php endif; ?>
	<div class="fd-product-card__body">
		<h3 class="fd-product-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<div class="fd-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php
		if ( function_exists( 'fandeco_installments_html' ) ) { // plugin Fan Deco Core (solo si está activado en sus ajustes)
			echo fandeco_installments_html( $product, 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en el plugin.
		}
		?>
	</div>
</article>
