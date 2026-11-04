<?php
/**
 * Menús de navegación.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		register_nav_menus(
			array(
				'primary'      => __( 'Menú principal', 'fan-deco-studio' ),
				'footer'       => __( 'Footer: navegación', 'fan-deco-studio' ),
				'footer_legal' => __( 'Footer: políticas legales', 'fan-deco-studio' ),
			)
		);
	}
);

/**
 * Fallback del menú principal cuando todavía no se asignó uno: Inicio + Tienda (si hay WooCommerce).
 */
function fandeco_menu_fallback(): void {
	$items = array( home_url( '/' ) => __( 'Inicio', 'fan-deco-studio' ) );
	if ( fandeco_has_woocommerce() ) {
		$items[ wc_get_page_permalink( 'shop' ) ] = __( 'Tienda', 'fan-deco-studio' );
	}
	echo '<ul class="fd-menu">';
	foreach ( $items as $url => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
