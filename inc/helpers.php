<?php
/**
 * Funciones auxiliares reutilizables por plantillas y bloques.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

function fandeco_has_woocommerce(): bool {
	return class_exists( 'WooCommerce' );
}

/** Lee una opción del Customizer con prefijo fandeco_. */
function fandeco_option( string $key, $default = '' ) {
	return get_theme_mod( 'fandeco_' . $key, $default );
}

/** Devuelve $value si está en $allowed; si no, $default. */
function fandeco_enum( $value, array $allowed, string $default ): string {
	return in_array( $value, $allowed, true ) ? (string) $value : $default;
}

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

/** Etiqueta <img> responsive de un adjunto, o cadena vacía. */
function fandeco_attachment_image( int $id, string $size = 'large', array $attr = array() ): string {
	if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	return (string) wp_get_attachment_image( $id, $size, false, $attr );
}

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

/** Logo: imagen cargada (claro/oscuro) o tipografía de reserva "fan DECO". */
function fandeco_logo( string $variant = 'dark' ): string {
	$custom_id = (int) get_theme_mod( 'custom_logo' );
	$light_id  = (int) fandeco_option( 'logo_light', 0 );
	$id        = ( 'light' === $variant && $light_id ) ? $light_id : $custom_id;
	$label     = get_bloginfo( 'name' );

	if ( $id && wp_attachment_is_image( $id ) ) {
		$img = wp_get_attachment_image(
			$id,
			'full',
			false,
			array(
				'class'    => 'fd-logo__img',
				'alt'      => $label,
				'decoding' => 'async',
			)
		);
		return '<span class="fd-logo fd-logo--img">' . $img . '</span>';
	}

	// Reserva tipográfica hasta que se cargue el logotipo oficial en Apariencia > Personalizar.
	return '<span class="fd-logo fd-logo--text" aria-label="' . esc_attr( $label ) . '"><span class="fd-logo__fan" aria-hidden="true">fan</span><span class="fd-logo__deco" aria-hidden="true">DECO</span></span>';
}

/** Redes sociales configuradas (etiqueta => URL). */
function fandeco_social_links(): array {
	$networks = array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'tiktok'    => 'TikTok',
		'pinterest' => 'Pinterest',
		'youtube'   => 'YouTube',
	);
	$out = array();
	foreach ( $networks as $key => $label ) {
		$url = esc_url( (string) fandeco_option( 'social_' . $key, '' ) );
		if ( $url ) {
			$out[ $label ] = $url;
		}
	}
	return $out;
}

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
