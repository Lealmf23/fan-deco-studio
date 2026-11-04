<?php
/**
 * Title: Fan Deco: Portada completa
 * Slug: fan-deco-studio/home
 * Description: Hero, beneficios, categorías, productos destacados, mascotas, configurador 3D (próximamente) y proceso.
 * Categories: fandeco
 * Keywords: home, portada, inicio
 * Viewport Width: 1400
 * Block Types: core/post-content
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$fd_shop = fandeco_has_woocommerce() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$fd_pets = $fd_shop;
if ( fandeco_has_woocommerce() ) {
	$fd_term = get_term_by( 'slug', 'mascotas', 'product_cat' );
	if ( $fd_term ) {
		$fd_link = get_term_link( $fd_term );
		$fd_pets = is_wp_error( $fd_link ) ? $fd_shop : $fd_link;
	}
}
?>
<!-- wp:fandeco/hero {"buttonUrl":<?php echo wp_json_encode( esc_url_raw( $fd_shop ) ); ?>} /-->

<!-- wp:fandeco/benefits /-->

<!-- wp:fandeco/section-heading {"title":"Comprá por categoría","linkText":"Ver todo","linkUrl":<?php echo wp_json_encode( esc_url_raw( $fd_shop ) ); ?>} /-->

<!-- wp:fandeco/category-grid /-->

<!-- wp:fandeco/section-heading {"title":"Productos destacados","linkText":"Ver toda la tienda","linkUrl":<?php echo wp_json_encode( esc_url_raw( $fd_shop ) ); ?>} /-->

<!-- wp:fandeco/featured-products /-->

<!-- wp:fandeco/editorial-banner {"eyebrow":"Personalizados","title":"Tu mascota, para siempre","description":"Convertimos a tu compañero en una pieza única, impresa en 3D y llena de detalles.","buttonText":"Ver mascotas","buttonUrl":<?php echo wp_json_encode( esc_url_raw( $fd_pets ) ); ?>,"variant":"wine","imagePosition":"left"} /-->

<!-- wp:fandeco/editorial-banner {"eyebrow":"Personalizador 3D","badge":"Próximamente","title":"Diseñá tu pieza en 3D","description":"Estamos preparando un configurador para ver tu producto en 360°, cambiar colores y acabados.","variant":"dark","mount":"configurator"} /-->

<!-- wp:fandeco/section-heading {"title":"Del diseño a tu casa","align":"center"} /-->

<!-- wp:fandeco/process-steps /-->
