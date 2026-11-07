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
function fandeco_menu_fallback( $args = array() ): void {
	$items = array( home_url( '/' ) => __( 'Inicio', 'fan-deco-studio' ) );
	if ( fandeco_has_woocommerce() ) {
		$items[ wc_get_page_permalink( 'shop' ) ] = __( 'Tienda', 'fan-deco-studio' );
	}
	$class = is_array( $args ) && ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'fd-menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $url => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}


/**
 * Elige un menú existente para una posición del tema cuando todavía no se asignó ninguno.
 * Principal y navegación del pie: el menú "principal" (o el primero con elementos que no sea del pie).
 * Políticas legales del pie: un menú cuyo nombre contenga "footer", "pie", "legal" o "polít".
 *
 * @param string                                   $location Posición: primary | footer | footer_legal.
 * @param array<int, object{term_id:int,name:string,slug:string,count:int}> $menus Menús existentes.
 * @return int ID del menú o 0 si no hay una buena opción.
 */
function fandeco_pick_menu( string $location, array $menus ): int {
	$is_footerish = static fn( $m ) => (bool) preg_match( '/footer|pie|legal|polit/i', $m->name . ' ' . $m->slug );
	$with_items   = array_values( array_filter( $menus, static fn( $m ) => (int) $m->count > 0 ) );
	if ( 'footer_legal' === $location ) {
		foreach ( $with_items as $m ) {
			if ( $is_footerish( $m ) ) {
				return (int) $m->term_id;
			}
		}
		return 0;
	}
	foreach ( $with_items as $m ) {
		if ( preg_match( '/main|principal|header|cabecera|primary/i', $m->name . ' ' . $m->slug ) && ! $is_footerish( $m ) ) {
			return (int) $m->term_id;
		}
	}
	foreach ( $with_items as $m ) {
		if ( ! $is_footerish( $m ) ) {
			return (int) $m->term_id;
		}
	}
	return 0;
}

/* Sin menú asignado a la posición: se usa uno existente en vez del menú de reserva (Inicio + Tienda). Si se asigna uno, manda el asignado. */
add_filter(
	'wp_nav_menu_args',
	static function ( $args ) {
		$loc = $args['theme_location'] ?? '';
		if ( ! in_array( $loc, array( 'primary', 'footer', 'footer_legal' ), true ) || ! empty( $args['menu'] ) || has_nav_menu( $loc ) ) {
			return $args;
		}
		$menu = fandeco_pick_menu( $loc, wp_get_nav_menus() );
		if ( $menu ) {
			$args['menu'] = $menu;
		}
		return $args;
	}
);

/* Al activar el tema por primera vez, deja asignados los menús existentes a sus posiciones (editable luego en Apariencia > Menús). */
add_action(
	'after_switch_theme',
	static function () {
		$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
		$menus     = wp_get_nav_menus();
		foreach ( array( 'primary', 'footer', 'footer_legal' ) as $loc ) {
			if ( empty( $locations[ $loc ] ) ) {
				$pick = fandeco_pick_menu( $loc, $menus );
				if ( $pick ) {
					$locations[ $loc ] = $pick;
				}
			}
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}
);
