<?php
/**
 * GENERADO por scripts/sync-blocks-plugin.mjs a partir del tema Fan Deco Studio. No editar a mano.
 * Funciones de apoyo de los bloques, definidas solo si no existen (el tema Fan Deco Studio ya las trae).
 *
 * @package FanDecoBlocks
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'fandeco_has_woocommerce' ) ) {
	function fandeco_has_woocommerce(): bool {
		return class_exists( 'WooCommerce' );
	}
}

if ( ! function_exists( 'fandeco_enum' ) ) {
	/** Devuelve $value si está en $allowed; si no, $default. */
	function fandeco_enum( $value, array $allowed, string $default ): string {
		return in_array( $value, $allowed, true ) ? (string) $value : $default;
	}
}

if ( ! function_exists( 'fandeco_icon_choices' ) ) {
	/** Iconos disponibles (clave => etiqueta). Mantener sincronizado con src/blocks/shared/icons.js */
	function fandeco_icon_choices(): array {
		return array(
			'truck'    => __( 'Envío', 'fan-deco-studio' ),
			'card'     => __( 'Tarjeta', 'fan-deco-studio' ),
			'bank'     => __( 'Transferencia', 'fan-deco-studio' ),
			'shield'   => __( 'Garantía', 'fan-deco-studio' ),
			'design'   => __( 'Diseño', 'fan-deco-studio' ),
			'test'     => __( 'Prueba', 'fan-deco-studio' ),
			'print'    => __( 'Fabricación', 'fan-deco-studio' ),
			'package'  => __( 'Paquete', 'fan-deco-studio' ),
			'sparkles' => __( 'Destacado', 'fan-deco-studio' ),
			'heart'    => __( 'Favorito', 'fan-deco-studio' ),
			'palette'  => __( 'Colores', 'fan-deco-studio' ),
			'bulb'     => __( 'Idea', 'fan-deco-studio' ),
			'tag'      => __( 'Oferta', 'fan-deco-studio' ),
			'gift'     => __( 'Regalo', 'fan-deco-studio' ),
		);
	}
}

if ( ! function_exists( 'fandeco_icon' ) ) {
	/** SVG inline decorativo (aria-hidden). */
	function fandeco_icon( string $name, string $class = 'fd-icon' ): string {
		static $paths = null;
		if ( null === $paths ) {
			$paths = array(
				'truck'       => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
				'card'        => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
				'bank'        => '<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
				'shield'      => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
				'design'      => '<path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>',
				'test'        => '<path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/><path d="M8.5 2h7"/><path d="M7 16h10"/>',
				'print'       => '<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/>',
				'package'     => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
				'sparkles'    => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>',
				'heart'       => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
				'palette'     => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
				'bulb'        => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
				'tag'         => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
				'gift'        => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>',
				// UI
				'search'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
				'user'        => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
				'bag'         => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
				'menu'        => '<path d="M4 6h16M4 12h16M4 18h16"/>',
				'close'       => '<path d="M18 6 6 18M6 6l12 12"/>',
				'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
				'check'       => '<path d="m5 12 5 5 9-10"/>',
				'pin'         => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
				'eye'         => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
				'share'       => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
				'link'        => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
				'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
				'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
				'clock'       => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
				'chat'        => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
				'home'        => '<path d="m3 10.5 9-7.5 9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
				'grid'        => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
				'chevron-l'   => '<path d="m15 18-6-6 6-6"/>',
				'chevron-r'   => '<path d="m9 18 6-6-6-6"/>',
			);
		}
		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}
		return sprintf(
			'<svg class="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
			esc_attr( $class ),
			$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput -- constante interna.
		);
	}
}

if ( ! function_exists( 'fandeco_attachment_image' ) ) {
	/** Etiqueta <img> responsive de un adjunto, o cadena vacía. */
	function fandeco_attachment_image( int $id, string $size = 'large', array $attr = array() ): string {
		if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
			return '';
		}
		return (string) wp_get_attachment_image( $id, $size, false, $attr );
	}
}

if ( ! function_exists( 'fandeco_picture' ) ) {
	/**
	 * <picture> con imagen desktop y (opcional) mobile. La imagen "priority" no usa lazy-loading.
	 *
	 * @param int    $desktop  ID adjunto desktop.
	 * @param int    $mobile   ID adjunto mobile (0 = usar desktop).
	 * @param string $class    Clases del <img>.
	 * @param bool   $priority true para LCP (fetchpriority=high, sin lazy).
	 */
	function fandeco_picture( int $desktop, int $mobile = 0, string $class = '', bool $priority = false ): string {
		$base = array(
			'class'    => $class,
			'decoding' => 'async',
			'sizes'    => '(max-width: 767px) 100vw, 60vw',
		);
		if ( $priority ) {
			$base['loading']       = 'eager';
			$base['fetchpriority'] = 'high';
		} else {
			$base['loading'] = 'lazy';
		}

		$img = fandeco_attachment_image( $desktop ? $desktop : $mobile, 'fandeco-hero', $base );
		if ( '' === $img ) {
			return '';
		}
		if ( $mobile && $desktop && wp_attachment_is_image( $mobile ) ) {
			$src    = wp_get_attachment_image_url( $mobile, 'fandeco-hero-mobile' );
			$srcset = wp_get_attachment_image_srcset( $mobile, 'fandeco-hero-mobile' );
			if ( $src ) {
				$img = sprintf(
					'<picture><source media="(max-width: 767px)" srcset="%s" sizes="100vw">%s</picture>',
					esc_attr( $srcset ? $srcset : $src ),
					$img
				);
			}
		}
		return $img;
	}
}

if ( ! function_exists( 'fandeco_button' ) ) {
	/** Botón/enlace con variantes de diseño. Devuelve '' si falta texto o URL. */
	function fandeco_button( string $label, string $url, string $variant = 'primary', string $extra_class = '' ): string {
		$label = trim( $label );
		$url   = esc_url( $url );
		if ( '' === $label || '' === $url ) {
			return '';
		}
		$variant = fandeco_enum( $variant, array( 'primary', 'secondary', 'outline', 'light' ), 'primary' );
		return sprintf(
			'<a class="fd-btn fd-btn--%1$s %2$s" href="%3$s">%4$s</a>',
			esc_attr( $variant ),
			esc_attr( $extra_class ),
			$url,
			esc_html( $label )
		);
	}
}

if ( ! function_exists( 'fandeco_block_attrs' ) ) {
	/** Atributos comunes del contenedor de un bloque (wrapper + animación). */
	function fandeco_block_attrs( string $name, array $attributes, string $extra_class = '' ): string {
		$animate = ! isset( $attributes['animate'] ) || ! empty( $attributes['animate'] );
		return get_block_wrapper_attributes(
			array(
				'class'           => trim( 'fd-section fd-' . $name . ' ' . $extra_class ),
				'data-fd-animate' => $animate ? 'true' : 'false',
			)
		);
	}
}

if ( ! function_exists( 'fandeco_title_html' ) ) {
	/**
	 * Título con dos estilos: **negrita** y *cursiva* (serif en color oliva). Todo lo demás se escapa.
	 * Ejemplo: "**Hacemos** *tus ideas* **realidad.**"
	 */
	function fandeco_title_html( string $text ): string {
		$html = esc_html( sanitize_text_field( $text ) );
		$html = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $html ) ?? $html;
		$html = preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', $html ) ?? $html;
		return $html;
	}
}

if ( ! function_exists( 'fandeco_product_badges' ) ) {
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
}

if ( ! function_exists( 'fandeco_loop_add_to_cart_html' ) ) {
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
}

if ( ! function_exists( 'fandeco_find_hex' ) ) {
	/** Busca recursivamente el primer color hexadecimal (#rgb, #rrggbb, #rrggbbaa) dentro de un valor. */
	function fandeco_find_hex( $value ): string {
		if ( is_string( $value ) ) {
			$value = trim( $value );
			return preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ? $value : '';
		}
		if ( is_object( $value ) ) {
			$value = (array) $value;
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				$found = fandeco_find_hex( $item );
				if ( '' !== $found ) {
					return $found;
				}
			}
		}
		return '';
	}
}

if ( ! function_exists( 'fandeco_color_names' ) ) {
	/** Colores de respaldo por nombre (español/inglés comunes). Se puede ampliar con el filtro `fandeco_color_names`. */
	function fandeco_color_names(): array {
		return (array) apply_filters(
			'fandeco_color_names',
			array(
				'negro' => '#111111', 'black' => '#111111', 'blanco' => '#ffffff', 'white' => '#ffffff',
				'gris' => '#8c9097', 'rojo' => '#c0272d', 'bordo' => '#682242', 'rosa' => '#e8a4b8', 'naranja' => '#e8812e',
				'amarillo' => '#f2c94c', 'verde' => '#3f8f5b', 'azul' => '#2f5fa8', 'celeste' => '#83a1b9',
				'violeta' => '#7a4fa3', 'lila' => '#b79ad6', 'marron' => '#6b4a35', 'beige' => '#d9c6a5',
				'dorado' => '#c9a24a', 'plateado' => '#c0c4c8', 'madera' => '#b98b5e', 'natural' => '#e6d8bd',
			)
		);
	}
}

if ( ! function_exists( 'fandeco_swatch_color' ) ) {
	/** Color de un término: 1) campo propio, 2) datos que dejó Blocksy (si existen), 3) nombre conocido. */
	function fandeco_swatch_color( $term ): string {
		if ( ! $term ) {
			return '';
		}
		$own = get_term_meta( $term->term_id, 'fandeco_swatch_color', true );
		if ( is_string( $own ) && '' !== fandeco_find_hex( $own ) ) {
			return fandeco_find_hex( $own );
		}
		// Blocksy guarda opciones de término en un meta serializado; clave/estructura no documentadas: búsqueda tolerante.
		$legacy = fandeco_find_hex( get_term_meta( $term->term_id, 'blocksy_taxonomy_meta_options', true ) );
		if ( '' !== $legacy ) {
			return $legacy;
		}
		$key   = sanitize_title( remove_accents( (string) $term->name ) );
		$names = fandeco_color_names();
		return isset( $names[ $key ] ) ? $names[ $key ] : '';
	}
}

if ( ! function_exists( 'fandeco_swatch_type' ) ) {
	/** Tipo visual de un atributo global: color | button | select. */
	function fandeco_swatch_type( string $taxonomy ): string {
		$raw = 'select';
		if ( function_exists( 'wc_attribute_taxonomy_id_by_name' ) && function_exists( 'wc_get_attribute' ) ) {
			$id   = wc_attribute_taxonomy_id_by_name( $taxonomy );
			$attr = $id ? wc_get_attribute( $id ) : null;
			if ( $attr && ! empty( $attr->type ) ) {
				$raw = (string) $attr->type;
			}
		}
		$map  = array( 'color' => 'color', 'button' => 'button', 'image' => 'button' ); // "image" (Blocksy) degrada a botón con texto.
		$type = $map[ $raw ] ?? 'select';
		return (string) apply_filters( 'fandeco_swatch_type', $type, $taxonomy );
	}
}

if ( ! function_exists( 'fandeco_variation_swatch_data' ) ) {
	/** Datos para las tarjetas de producto: nombre + color (si el atributo es de tipo color). */
	function fandeco_variation_swatch_data( WC_Product $product ): array {
		if ( ! $product instanceof WC_Product_Variable ) {
			return array();
		}
		$attrs = $product->get_variation_attributes();
		$key   = key( $attrs );
		$first = reset( $attrs );
		if ( ! $first || ! $key ) {
			return array();
		}
		$is_color = taxonomy_exists( $key ) && 'color' === fandeco_swatch_type( $key );
		$out      = array();
		foreach ( $first as $value ) {
			$term  = taxonomy_exists( $key ) ? get_term_by( 'slug', $value, $key ) : null;
			$out[] = array(
				'name'  => $term ? (string) $term->name : (string) $value,
				'color' => ( $is_color && $term ) ? fandeco_swatch_color( $term ) : '',
			);
		}
		return $out;
	}
}
