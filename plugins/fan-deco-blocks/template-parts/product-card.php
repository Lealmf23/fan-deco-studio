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
$max_dots  = 8;
$opts      = is_array( $args['opts'] ?? null ) ? $args['opts'] : array();
$show_dots = ! isset( $opts['swatches'] ) || ! empty( $opts['swatches'] );
$show_alt  = ! isset( $opts['swap'] ) || ! empty( $opts['swap'] );
$show_tr   = ! isset( $opts['transfer'] ) || ! empty( $opts['transfer'] );

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
$alt_id  = ( $show_alt && $alt_id && (int) $alt_id !== (int) $image_id && wp_attachment_is_image( $alt_id ) ) ? $alt_id : 0;
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
			if ( $alt_id ) { // la segunda foto no se descarga hasta que el mouse pasa por encima (ahorra datos y velocidad)
				$alt_src    = wp_get_attachment_image_url( $alt_id, 'fandeco-card' );
				$alt_srcset = wp_get_attachment_image_srcset( $alt_id, 'fandeco-card' );
				if ( $alt_src ) {
					printf(
						'<img class="fd-product-card__alt" alt="" decoding="async" data-src="%1$s"%2$s sizes="(max-width: 767px) 50vw, (max-width: 1023px) 33vw, 25vw">',
						esc_url( $alt_src ),
						$alt_srcset ? ' data-srcset="' . esc_attr( $alt_srcset ) . '"' : ''
					);
				}
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
		<?php
		$fd_dots = $show_dots ? array_values( array_filter( $options, static fn( $o ) => ! empty( $o['color'] ) ) ) : array();
		if ( $fd_dots ) : // un punto por cada variante de color; al pasar el mouse (o tocar) se ve la foto de esa variante
			?>
			<ul class="fd-product-card__options" aria-label="<?php esc_attr_e( 'Colores disponibles', 'fan-deco-studio' ); ?>">
				<?php foreach ( array_slice( $fd_dots, 0, $max_dots ) as $option ) : ?>
					<?php $dot_img = ! empty( $option['image_id'] ) ? wp_get_attachment_image_url( (int) $option['image_id'], 'fandeco-card' ) : ''; ?>
					<li class="fd-dot" style="--fd-swatch:<?php echo esc_attr( $option['color'] ); ?>" title="<?php echo esc_attr( $option['name'] ); ?>"<?php echo $dot_img ? ' data-fd-img="' . esc_url( $dot_img ) . '" tabindex="0" role="button"' : ''; ?>><span class="screen-reader-text"><?php echo esc_html( $option['name'] ); ?></span></li>
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
		<?php if ( $show_cart ) : // botón circular que lleva a la ficha del producto (no agrega al carrito) ?>
			<div class="fd-product-card__add"><a class="button fd-product-card__go product_type_variable" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: nombre del producto */ __( 'Ver producto: %s', 'fan-deco-studio' ), $product->get_name() ) ); ?>"><?php esc_html_e( 'Ver producto', 'fan-deco-studio' ); ?></a></div>
		<?php endif; ?>
	<div class="fd-product-card__body">
		<h3 class="fd-product-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<div class="fd-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php
		if ( $show_tr && function_exists( 'fandeco_transfer_price_html' ) ) { // plugin Fan Deco Core: solo el precio con transferencia, sin cuotas
			echo fandeco_transfer_price_html( $product, 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en el plugin.
		}
		?>
	</div>
</article>
