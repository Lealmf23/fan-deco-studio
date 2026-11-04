<?php
/**
 * Integración con WooCommerce: soporte oficial, wrappers y fragmentos del carrito.
 * No modifica precios, descuentos, impuestos, pedidos ni pasarelas.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 800,
				'single_image_width'    => 1100,
				'product_grid'          => array(
					'default_rows'    => 3,
					'min_rows'        => 1,
					'default_columns' => 4,
					'min_columns'     => 2,
					'max_columns'     => 4,
				),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}
);

if ( ! fandeco_has_woocommerce() ) {
	// Todo lo demás solo tiene sentido con WooCommerce activo.
	return;
}

/** Cantidad de ítems del carrito (0 si el carrito aún no está disponible). */
function fandeco_cart_count(): int {
	return ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/** Enlace al carrito con contador; se refresca por fragmentos de WooCommerce. */
function fandeco_cart_link_html(): string {
	$count = fandeco_cart_count();
	return sprintf(
		'<a class="fd-icon-btn fd-cart-link" href="%1$s" aria-label="%2$s">%3$s<span class="fd-cart-count" data-count="%4$d"%5$s>%4$d</span></a>',
		esc_url( wc_get_cart_url() ),
		/* translators: %d: cantidad de productos */
		esc_attr( sprintf( _n( 'Carrito, %d producto', 'Carrito, %d productos', $count, 'fan-deco-studio' ), $count ) ),
		fandeco_icon( 'bag' ),
		$count,
		$count > 0 ? '' : ' hidden'
	);
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( array $fragments ): array {
		$fragments['a.fd-cart-link'] = fandeco_cart_link_html();
		return $fragments;
	}
);

// Wrappers propios (en lugar de los de WooCommerce).
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action(
	'woocommerce_before_main_content',
	static function () {
		echo '<main id="main" class="fd-main fd-woo" tabindex="-1"><div class="fd-container">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	static function () {
		echo '</div></main>';
	},
	10
);

add_filter( 'loop_shop_columns', static fn() => 4 );
add_filter( 'woocommerce_output_related_products_args', static fn( array $args ): array => array_merge( $args, array( 'posts_per_page' => 4, 'columns' => 4 ) ) );

add_filter(
	'woocommerce_breadcrumb_defaults',
	static function ( array $defaults ): array {
		$defaults['delimiter']   = ' <span aria-hidden="true">/</span> ';
		$defaults['wrap_before'] = '<nav class="fd-breadcrumb woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Migas de pan', 'fan-deco-studio' ) . '">';
		$defaults['wrap_after']  = '</nav>';
		return $defaults;
	}
);

/**
 * Etiquetas comerciales de un producto (solo datos reales: oferta, destacado, sin stock).
 *
 * @return string[]
 */
function fandeco_product_badges( WC_Product $product ): array {
	$badges = array();
	if ( ! $product->is_in_stock() ) {
		$badges[] = __( 'Sin stock', 'fan-deco-studio' );
	} elseif ( $product->is_on_sale() ) {
		$badges[] = __( 'Oferta', 'fan-deco-studio' );
	}
	if ( $product->is_featured() && $product->is_in_stock() ) {
		$badges[] = __( 'Destacado', 'fan-deco-studio' );
	}
	return $badges;
}

/**
 * Valores del primer atributo usado para variaciones (p. ej. colores) de un producto variable.
 *
 * @return string[] Nombres legibles.
 */
function fandeco_variation_options( WC_Product $product ): array {
	if ( ! $product instanceof WC_Product_Variable ) {
		return array();
	}
	$attrs = $product->get_variation_attributes();
	$first = reset( $attrs );
	$key   = key( $attrs );
	if ( ! $first || ! $key ) {
		return array();
	}
	$out = array();
	foreach ( $first as $value ) {
		$out[] = taxonomy_exists( $key ) && ( $term = get_term_by( 'slug', $value, $key ) ) ? $term->name : $value; // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
	}
	return $out;
}

// Barra de herramientas del archivo: resultado + orden en una fila.
add_action( 'woocommerce_before_shop_loop', static fn() => print( '<div class="fd-shop-toolbar">' ), 15 );
add_action( 'woocommerce_before_shop_loop', static fn() => print( '</div>' ), 35 );

// Texto alternativo de respaldo en imágenes de producto sin alt (accesibilidad + SEO de imágenes).
add_filter(
	'wp_get_attachment_image_attributes',
	static function ( array $attr, WP_Post $attachment ): array {
		if ( empty( $attr['alt'] ) && 'product' === get_post_type( $attachment->post_parent ) ) {
			$attr['alt'] = get_the_title( $attachment->post_parent );
		}
		return $attr;
	},
	10,
	2
);
