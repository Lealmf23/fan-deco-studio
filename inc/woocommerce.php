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

/** Carrito de la barra inferior móvil (ícono + texto + contador). Fragmento propio. */
function fandeco_tab_cart_html( bool $current = false ): string {
	$count = fandeco_cart_count();
	return sprintf(
		'<a class="fd-tab fd-tab-cart" href="%1$s"%2$s>%3$s<span class="fd-tab__label">%4$s</span><span class="fd-cart-count" data-count="%5$d"%6$s>%5$d</span></a>',
		esc_url( wc_get_cart_url() ),
		$current ? ' aria-current="page"' : '',
		fandeco_icon( 'bag' ),
		esc_html__( 'Carrito', 'fan-deco-studio' ),
		$count,
		$count > 0 ? '' : ' hidden'
	);
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( array $fragments ): array {
		$fragments['a.fd-cart-link'] = fandeco_cart_link_html();
		$fragments['a.fd-tab-cart']  = fandeco_tab_cart_html( is_cart() );
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

/* ---- Tarjeta de producto del tema en los listados de WooCommerce ----
 * Se reemplazan los ganchos de WooCommerce que arman cada ítem por UNA tarjeta del tema. El botón de compra
 * sigue saliendo de woocommerce_template_loop_add_to_cart() (con su filtro `woocommerce_loop_add_to_cart_link`),
 * para no romper plugins de seguimiento/AJAX que dependen de ese marcado.
 */
add_action(
	'init',
	static function () {
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		// La tarjeta ya incluye las cuotas del plugin Fan Deco Core: se evita mostrarlas dos veces.
		if ( class_exists( 'FanDeco_Installments' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', array( 'FanDeco_Installments', 'render_loop' ), 11 );
		}
		add_action( 'woocommerce_before_shop_loop_item', 'fandeco_loop_card', 10 );
		// En la ficha, la etiqueta "Oferta" va junto a la categoría (ver más abajo), no flotando sobre la galería.
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	},
	30
);

function fandeco_loop_card(): void {
	global $product;
	if ( $product instanceof WC_Product ) {
		get_template_part( 'template-parts/components/product-card', null, array( 'product' => $product, 'show_cart' => true ) );
	}
}

/** Botón de compra del listado (salida oficial de WooCommerce) para un producto concreto. */
function fandeco_loop_add_to_cart_html( WC_Product $product ): string {
	if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
		return '';
	}
	$previous           = $GLOBALS['product'] ?? null;
	$GLOBALS['product'] = $product; // la función de WooCommerce lee el producto global
	ob_start();
	woocommerce_template_loop_add_to_cart();
	$html = (string) ob_get_clean();
	$GLOBALS['product'] = $previous;
	return $html;
}

/* ---- Categorías reales como filtros de la tienda ---- */
add_action(
	'woocommerce_before_shop_loop',
	static function () {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'exclude'    => (int) get_option( 'default_product_cat' ),
				'orderby'    => 'menu_order',
			)
		);
		if ( is_wp_error( $terms ) || count( $terms ) < 2 ) {
			return;
		}
		$current = is_product_category() ? (int) get_queried_object_id() : 0;
		echo '<nav class="fd-shop-cats" aria-label="' . esc_attr__( 'Categorías', 'fan-deco-studio' ) . '"><ul>';
		printf(
			'<li><a href="%s"%s>%s</a></li>',
			esc_url( wc_get_page_permalink( 'shop' ) ),
			$current ? '' : ' aria-current="page"',
			esc_html__( 'Todo', 'fan-deco-studio' )
		);
		foreach ( $terms as $term ) {
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			printf(
				'<li><a href="%s"%s>%s</a></li>',
				esc_url( $link ),
				( $current === (int) $term->term_id ) ? ' aria-current="page"' : '',
				esc_html( $term->name )
			);
		}
		echo '</ul></nav>';
	},
	5
);

/* ---- Ficha de producto: categoría sobre el título (dato real) ---- */
add_action(
	'woocommerce_single_product_summary',
	static function () {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		echo '<div class="fd-product-flags">';
		if ( $product->is_on_sale() && $product->is_in_stock() ) {
			echo '<span class="fd-product-flag">' . esc_html__( 'Oferta', 'fan-deco-studio' ) . '</span>';
		}
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			echo '</div>';
			return;
		}
		foreach ( $terms as $term ) {
			if ( (int) $term->term_id === (int) get_option( 'default_product_cat' ) ) {
				continue;
			}
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				printf( '<a class="fd-product-cat" href="%s">%s</a>', esc_url( $link ), esc_html( $term->name ) );
			}
			break;
		}
		echo '</div>';
	},
	4
);

/* ---- Barra fija de compra (móvil) al pie de la ficha: nombre, precio y botón; el JS la muestra cuando el botón real sale de pantalla ---- */
add_action(
	'woocommerce_after_single_product',
	static function () {
		global $product;
		if ( ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
			return;
		}
		printf(
			'<div class="fd-buybar" data-fd-buybar hidden><div class="fd-buybar__info"><strong class="fd-buybar__name">%1$s</strong><span class="fd-buybar__price" data-fd-buybar-price>%2$s</span><span class="fd-buybar__transfer" data-fd-buybar-transfer hidden></span></div><button type="button" class="fd-btn fd-btn--primary fd-buybar__btn" data-fd-buybar-add>%3$s</button></div>',
			esc_html( $product->get_name() ),
			wp_kses_post( $product->get_price_html() ),
			esc_html__( 'Agregar', 'fan-deco-studio' )
		);
	},
	20
);

/* ---- Indicador de pasos (solo visual): Carrito -> Datos y pago -> Listo ----
 * Se agrega con ganchos de WooCommerce; no modifica el flujo, los campos ni las pasarelas.
 */
function fandeco_checkout_steps( int $current ): void {
	$steps = array(
		1 => __( 'Carrito', 'fan-deco-studio' ),
		2 => __( 'Datos y pago', 'fan-deco-studio' ),
		3 => __( 'Listo', 'fan-deco-studio' ),
	);
	echo '<ol class="fd-stepper" aria-label="' . esc_attr__( 'Progreso de la compra', 'fan-deco-studio' ) . '">';
	foreach ( $steps as $n => $label ) {
		$state = $n < $current ? 'is-done' : ( $n === $current ? 'is-current' : '' );
		printf(
			'<li class="fd-stepper__item %1$s"%2$s><span class="fd-stepper__dot" aria-hidden="true">%3$s</span><span class="fd-stepper__label">%4$s</span></li>',
			esc_attr( $state ),
			$n === $current ? ' aria-current="step"' : '',
			$n < $current ? fandeco_icon( 'check' ) : (int) $n, // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html( $label )
		);
	}
	echo '</ol>';
}
add_action( 'woocommerce_before_cart', static fn() => fandeco_checkout_steps( 1 ), 5 );
add_action( 'woocommerce_before_checkout_form', static fn() => fandeco_checkout_steps( 2 ), 5 );
add_action( 'woocommerce_before_thankyou', static fn() => fandeco_checkout_steps( 3 ), 5 );

/* ---- Mi cuenta: añadidos visuales por ganchos oficiales (no se tocan formularios ni plantillas) ---- */

/** Panel de bienvenida sobre los formularios de acceso/registro. */
add_action(
	'woocommerce_before_customer_login_form',
	static function () {
		echo '<div class="fd-account-intro"><h2 class="fd-account-intro__title">' . esc_html__( 'Tu cuenta Fan Deco', 'fan-deco-studio' ) . '</h2><p>' . esc_html__( 'Seguí tus pedidos, guardá tus direcciones y comprá más rápido.', 'fan-deco-studio' ) . '</p></div>';
	},
	5
);

/** Accesos rápidos del escritorio de la cuenta, a partir de los endpoints reales y habilitados. */
add_action(
	'woocommerce_account_dashboard',
	static function () {
		$items = function_exists( 'wc_get_account_menu_items' ) ? wc_get_account_menu_items() : array();
		$wanted = array(
			'orders'       => array( 'package', __( 'Ver mis pedidos y su estado', 'fan-deco-studio' ) ),
			'downloads'    => array( 'tag', __( 'Tus descargas', 'fan-deco-studio' ) ),
			'edit-address' => array( 'pin', __( 'Direcciones de envío y facturación', 'fan-deco-studio' ) ),
			'edit-account' => array( 'user', __( 'Datos de la cuenta y contraseña', 'fan-deco-studio' ) ),
		);
		$cards = '';
		foreach ( $wanted as $endpoint => $meta ) {
			if ( ! isset( $items[ $endpoint ] ) ) {
				continue;
			}
			$cards .= sprintf(
				'<a class="fd-account-card" href="%1$s"><span class="fd-account-card__icon" aria-hidden="true">%2$s</span><strong>%3$s</strong><span>%4$s</span></a>',
				esc_url( wc_get_account_endpoint_url( $endpoint ) ),
				fandeco_icon( $meta[0], 'fd-icon' ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $items[ $endpoint ] ),
				esc_html( $meta[1] )
			);
		}
		if ( $cards ) {
			echo '<div class="fd-account-cards">' . $cards . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado arriba.
		}
	},
	20
);
