<?php
/**
 * Sistema de diseño personalizable (Apariencia > Personalizar > Fan Deco Studio).
 *
 * Un único esquema (`fandeco_design_schema()`) define cada ajuste: tipo, límites, valor por defecto y la variable CSS
 * que controla. De ese esquema salen los controles del Personalizador, el CSS de salida (solo lo que se cambió) y la
 * vista previa en vivo (assets/js/design-preview.js, que replica la misma lógica; hay una prueba de paridad).
 * El CSS del tema lee estas variables con un valor de reserva, así que sin cambios el sitio se ve igual.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

/** Familias tipográficas disponibles (sin cargar nada de afuera: la incluida y fuentes del sistema). */
function fandeco_design_fonts(): array {
	return array(
		'dmsans'   => array( 'DM Sans (incluida en el tema)', '"DM Sans Variable", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif' ),
		'system'   => array( 'Sistema (la del dispositivo)', 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' ),
		'arial'    => array( 'Arial / Helvetica', 'Arial, Helvetica, sans-serif' ),
		'trebuchet' => array( 'Trebuchet', '"Trebuchet MS", Tahoma, sans-serif' ),
		'verdana'  => array( 'Verdana', 'Verdana, Geneva, sans-serif' ),
		'georgia'  => array( 'Georgia (serif)', 'Georgia, "Times New Roman", serif' ),
		'times'    => array( 'Times (serif)', '"Times New Roman", Times, serif' ),
		'palatino' => array( 'Palatino (serif)', '"Palatino Linotype", Palatino, "Book Antiqua", serif' ),
		'garamond' => array( 'Garamond (serif)', 'Garamond, "Hoefler Text", "Times New Roman", serif' ),
		'cambria'  => array( 'Cambria (serif)', 'Cambria, Georgia, serif' ),
		'mono'     => array( 'Courier (monoespaciada)', '"Courier New", Courier, monospace' ),
	);
}

/**
 * Esquema de ajustes. Cada sección => campos. Tipos: color, range, select, toggle.
 * - vars: variable CSS => plantilla del valor ({v}). Para 'select' con 'map', {v} es el valor mapeado.
 * - emit => 'ratio': el valor se muestra en % y se emite dividido por 100.
 * - media => 'mobile': el valor se emite solo en pantallas de hasta 767 px.
 * - class_off (toggle): clase que se agrega al <body> cuando está apagado.
 */
function fandeco_design_schema(): array {
	static $schema = null;
	if ( null !== $schema ) {
		return $schema;
	}
	$font_choices = array();
	$font_map     = array();
	foreach ( fandeco_design_fonts() as $k => $f ) {
		$font_choices[ $k ] = $f[0];
		$font_map[ $k ]     = $f[1];
	}
	$weights = array(
		'300' => 'Fina (300)',
		'400' => 'Normal (400)',
		'500' => 'Media (500)',
		'600' => 'Seminegrita (600)',
		'700' => 'Negrita (700)',
		'800' => 'Extranegrita (800)',
		'900' => 'Negra (900)',
	);
	$schema = array(
		'colors'   => array(
			'title'  => __( 'Colores', 'fan-deco-studio' ),
			'desc'   => __( 'Los colores de todo el sitio. Los campos vacíos usan el color del tema. Cada color tiene un botón para volver al original.', 'fan-deco-studio' ),
			'fields' => array(
				'c_bg'        => array( 'type' => 'color', 'label' => __( 'Fondo del sitio', 'fan-deco-studio' ), 'default' => '#f5f2ed', 'vars' => array( '--color-fd-cream' => '{v}' ) ),
				'c_surface'   => array( 'type' => 'color', 'label' => __( 'Superficies suaves (fondos de fotos, recuadros)', 'fan-deco-studio' ), 'default' => '#e9e6dc', 'vars' => array( '--color-fd-sand' => '{v}' ) ),
				'c_text'      => array( 'type' => 'color', 'label' => __( 'Texto principal', 'fan-deco-studio' ), 'default' => '#292824', 'vars' => array( '--color-fd-ink' => '{v}' ) ),
				'c_heading'   => array( 'type' => 'color', 'label' => __( 'Títulos y secciones oscuras', 'fan-deco-studio' ), 'default' => '#292824', 'vars' => array( '--color-fd-blue' => '{v}', '--fd-night' => '{v}' ) ),
				'c_muted'     => array( 'type' => 'color', 'label' => __( 'Texto secundario', 'fan-deco-studio' ), 'default' => '#6c6a5f', 'vars' => array( '--color-fd-muted' => '{v}' ) ),
				'c_line'      => array( 'type' => 'color', 'label' => __( 'Líneas y bordes', 'fan-deco-studio' ), 'default' => '#e2dfd3', 'vars' => array( '--color-fd-line' => '{v}' ) ),
				'c_accent'    => array( 'type' => 'color', 'label' => __( 'Acento principal (botones, enlaces, precios)', 'fan-deco-studio' ), 'default' => '#626545', 'vars' => array( '--color-fd-wine' => '{v}' ) ),
				'c_accent_dk' => array( 'type' => 'color', 'label' => __( 'Acento al pasar el cursor', 'fan-deco-studio' ), 'default' => '#4d5036', 'vars' => array( '--color-fd-wine-dark' => '{v}' ) ),
				'c_accent_lt' => array( 'type' => 'color', 'label' => __( 'Acento claro (sobre fondos oscuros)', 'fan-deco-studio' ), 'default' => '#a6aa86', 'vars' => array( '--color-fd-steel' => '{v}' ) ),
				'c_em'        => array( 'type' => 'color', 'label' => __( 'Palabra en cursiva de los títulos', 'fan-deco-studio' ), 'default' => '#74784e', 'vars' => array( '--color-fd-olive' => '{v}' ) ),
				'c_btn_bg'    => array( 'type' => 'color', 'label' => __( 'Botones: fondo (vacío = acento principal)', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-btn-bg' => '{v}' ) ),
				'c_btn_text'  => array( 'type' => 'color', 'label' => __( 'Botones: texto', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-btn-text' => '{v}' ) ),
				'c_head_bg'   => array( 'type' => 'color', 'label' => __( 'Header: fondo (vacío = fondo del sitio)', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-header-bg' => '{v}' ) ),
				'c_head_text' => array( 'type' => 'color', 'label' => __( 'Header: texto e íconos', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-header-text' => '{v}' ) ),
				'c_ann_bg'    => array( 'type' => 'color', 'label' => __( 'Barra superior: fondo', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-announce-bg' => '{v}' ) ),
				'c_ann_text'  => array( 'type' => 'color', 'label' => __( 'Barra superior: texto', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-announce-text' => '{v}' ) ),
				'c_foot_bg'   => array( 'type' => 'color', 'label' => __( 'Footer: fondo (vacío = títulos y secciones oscuras)', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-footer-bg' => '{v}' ) ),
				'c_foot_text' => array( 'type' => 'color', 'label' => __( 'Footer: texto', 'fan-deco-studio' ), 'default' => '', 'vars' => array( '--fd-footer-text' => '{v}' ) ),
			),
		),
		'type'     => array(
			'title'  => __( 'Tipografía', 'fan-deco-studio' ),
			'desc'   => __( 'Fuentes, tamaños, pesos e interlineado. Usa la fuente incluida o fuentes del sistema (no se cargan fuentes de internet, para que el sitio sea rápido y privado).', 'fan-deco-studio' ),
			'fields' => array(
				'f_body'      => array( 'type' => 'select', 'label' => __( 'Fuente del texto', 'fan-deco-studio' ), 'default' => 'dmsans', 'choices' => $font_choices, 'map' => $font_map, 'vars' => array( '--font-sans' => '{v}' ) ),
				'f_heading'   => array( 'type' => 'select', 'label' => __( 'Fuente de los títulos', 'fan-deco-studio' ), 'default' => 'dmsans', 'choices' => $font_choices, 'map' => $font_map, 'vars' => array( '--font-display' => '{v}' ) ),
				'f_accent'    => array( 'type' => 'select', 'label' => __( 'Fuente de la palabra destacada (*cursiva*)', 'fan-deco-studio' ), 'default' => 'georgia', 'choices' => $font_choices, 'map' => $font_map, 'vars' => array( '--font-serif' => '{v}' ) ),
				'f_accent_st' => array( 'type' => 'select', 'label' => __( 'Estilo de la palabra destacada', 'fan-deco-studio' ), 'default' => 'italic', 'choices' => array( 'italic' => __( 'Cursiva', 'fan-deco-studio' ), 'normal' => __( 'Normal (sin cursiva)', 'fan-deco-studio' ) ), 'vars' => array( '--fd-em-style' => '{v}' ) ),
				'f_accent_wt' => array( 'type' => 'select', 'label' => __( 'Peso de la palabra destacada', 'fan-deco-studio' ), 'default' => '500', 'choices' => $weights, 'vars' => array( '--fd-em-weight' => '{v}' ) ),
				'f_size'      => array( 'type' => 'range', 'label' => __( 'Tamaño base del texto (%)', 'fan-deco-studio' ), 'default' => 100, 'min' => 80, 'max' => 125, 'step' => 1, 'vars' => array( '--fd-font-size' => '{v}%' ) ),
				'f_line'      => array( 'type' => 'range', 'label' => __( 'Interlineado del texto', 'fan-deco-studio' ), 'default' => 1.6, 'min' => 1.2, 'max' => 2.1, 'step' => 0.05, 'vars' => array( '--fd-line' => '{v}' ) ),
				't_scale'     => array( 'type' => 'range', 'label' => __( 'Tamaño de los títulos: escritorio (%)', 'fan-deco-studio' ), 'default' => 100, 'min' => 50, 'max' => 150, 'step' => 1, 'emit' => 'ratio', 'vars' => array( '--fd-title-scale' => '{v}' ) ),
				't_scale_m'   => array( 'type' => 'range', 'label' => __( 'Tamaño de los títulos: móvil (%)', 'fan-deco-studio' ), 'default' => 100, 'min' => 50, 'max' => 150, 'step' => 1, 'emit' => 'ratio', 'media' => 'mobile', 'vars' => array( '--fd-title-scale' => '{v}' ) ),
				't_weight'    => array( 'type' => 'select', 'label' => __( 'Peso de los títulos', 'fan-deco-studio' ), 'default' => '400', 'choices' => $weights, 'vars' => array( '--fd-title-weight' => '{v}' ) ),
				't_strong'    => array( 'type' => 'select', 'label' => __( 'Peso de la parte en **negrita**', 'fan-deco-studio' ), 'default' => '700', 'choices' => $weights, 'vars' => array( '--fd-title-strong' => '{v}' ) ),
				't_lh'        => array( 'type' => 'range', 'label' => __( 'Interlineado de los títulos', 'fan-deco-studio' ), 'default' => 0.92, 'min' => 0.8, 'max' => 1.4, 'step' => 0.01, 'vars' => array( '--fd-title-lh' => '{v}' ) ),
				't_ls'        => array( 'type' => 'range', 'label' => __( 'Espaciado entre letras de los títulos (em)', 'fan-deco-studio' ), 'default' => -0.05, 'min' => -0.08, 'max' => 0.05, 'step' => 0.005, 'vars' => array( '--fd-title-ls' => '{v}em' ) ),
				'e_ls'        => array( 'type' => 'range', 'label' => __( 'Sobretítulos: espaciado entre letras (em)', 'fan-deco-studio' ), 'default' => 0.22, 'min' => 0, 'max' => 0.5, 'step' => 0.01, 'vars' => array( '--fd-eyebrow-ls' => '{v}em' ) ),
				'e_case'      => array( 'type' => 'select', 'label' => __( 'Sobretítulos: mayúsculas', 'fan-deco-studio' ), 'default' => 'uppercase', 'choices' => array( 'uppercase' => __( 'Todo en mayúsculas', 'fan-deco-studio' ), 'none' => __( 'Como se escribe', 'fan-deco-studio' ) ), 'vars' => array( '--fd-eyebrow-case' => '{v}' ) ),
			),
		),
		'layout'   => array(
			'title'  => __( 'Diseño y espaciado', 'fan-deco-studio' ),
			'desc'   => __( 'Anchos, márgenes, espacio entre secciones y esquinas.', 'fan-deco-studio' ),
			'fields' => array(
				'l_container' => array( 'type' => 'range', 'label' => __( 'Ancho máximo del contenido (px)', 'fan-deco-studio' ), 'default' => 1280, 'min' => 960, 'max' => 1680, 'step' => 20, 'vars' => array( '--container-fd' => '{v}px' ) ),
				'l_gutter'    => array( 'type' => 'range', 'label' => __( 'Margen lateral en px (0 = automático)', 'fan-deco-studio' ), 'default' => 0, 'min' => 0, 'max' => 80, 'step' => 2, 'vars' => array( '--spacing-fd-gutter' => '{v}px' ) ),
				'l_section'   => array( 'type' => 'range', 'label' => __( 'Espacio entre secciones (%)', 'fan-deco-studio' ), 'default' => 100, 'min' => 40, 'max' => 200, 'step' => 5, 'emit' => 'ratio', 'vars' => array( '--fd-section-scale' => '{v}' ) ),
				'l_radius'    => array( 'type' => 'select', 'label' => __( 'Esquinas de tarjetas, fotos y ventanas', 'fan-deco-studio' ), 'default' => 'normal', 'choices' => array( 'square' => __( 'Rectas', 'fan-deco-studio' ), 'soft' => __( 'Suaves', 'fan-deco-studio' ), 'normal' => __( 'Redondeadas (por defecto)', 'fan-deco-studio' ), 'round' => __( 'Muy redondeadas', 'fan-deco-studio' ) ), 'map' => array( 'square' => '0', 'soft' => '0.5rem', 'normal' => '1.5rem', 'round' => '2.5rem' ), 'vars' => array( '--radius-fd-window' => '{v}', '--radius-fd-lg' => '{v}' ) ),
				'b_radius'    => array( 'type' => 'select', 'label' => __( 'Forma de los botones', 'fan-deco-studio' ), 'default' => 'pill', 'choices' => array( 'square' => __( 'Rectos', 'fan-deco-studio' ), 'soft' => __( 'Esquinas suaves', 'fan-deco-studio' ), 'round' => __( 'Redondeados', 'fan-deco-studio' ), 'pill' => __( 'Píldora (por defecto)', 'fan-deco-studio' ) ), 'map' => array( 'square' => '0', 'soft' => '0.5rem', 'round' => '1rem', 'pill' => '999px' ), 'vars' => array( '--fd-btn-radius' => '{v}' ) ),
				'b_height'    => array( 'type' => 'range', 'label' => __( 'Alto de los botones (rem)', 'fan-deco-studio' ), 'default' => 3, 'min' => 2.25, 'max' => 4.5, 'step' => 0.125, 'vars' => array( '--fd-btn-h' => '{v}rem' ) ),
			),
		),
		'header'   => array(
			'title'  => __( 'Header, menú y logo', 'fan-deco-studio' ),
			'desc'   => __( 'Tamaño del logo y del header en escritorio y móvil, y el aspecto del menú. El logo se sube en "Marca y header".', 'fan-deco-studio' ),
			'fields' => array(
				'h_logo'      => array( 'type' => 'range', 'label' => __( 'Alto del logo: escritorio (px)', 'fan-deco-studio' ), 'default' => 44, 'min' => 20, 'max' => 140, 'step' => 2, 'vars' => array( '--fd-logo-h' => '{v}px' ) ),
				'h_logo_m'    => array( 'type' => 'range', 'label' => __( 'Alto del logo: móvil (px)', 'fan-deco-studio' ), 'default' => 44, 'min' => 18, 'max' => 100, 'step' => 2, 'vars' => array( '--fd-logo-h-m' => '{v}px' ) ),
				'h_logo_foot' => array( 'type' => 'range', 'label' => __( 'Alto del logo en el footer (px)', 'fan-deco-studio' ), 'default' => 48, 'min' => 20, 'max' => 160, 'step' => 2, 'vars' => array( '--fd-logo-h-footer' => '{v}px' ) ),
				'h_height'    => array( 'type' => 'range', 'label' => __( 'Alto del header: escritorio (px)', 'fan-deco-studio' ), 'default' => 80, 'min' => 56, 'max' => 140, 'step' => 2, 'vars' => array( '--fd-header-h' => '{v}px' ) ),
				'h_height_m'  => array( 'type' => 'range', 'label' => __( 'Alto del header: móvil (px)', 'fan-deco-studio' ), 'default' => 60, 'min' => 48, 'max' => 110, 'step' => 2, 'vars' => array( '--fd-header-h-m' => '{v}px' ) ),
				'h_menu_size' => array( 'type' => 'range', 'label' => __( 'Tamaño de letra del menú (px)', 'fan-deco-studio' ), 'default' => 15.5, 'min' => 12, 'max' => 22, 'step' => 0.5, 'vars' => array( '--fd-menu-size' => '{v}px' ) ),
				'h_menu_gap'  => array( 'type' => 'range', 'label' => __( 'Separación entre enlaces del menú (px)', 'fan-deco-studio' ), 'default' => 40, 'min' => 12, 'max' => 80, 'step' => 2, 'vars' => array( '--fd-menu-gap' => '{v}px' ) ),
				'h_menu_wt'   => array( 'type' => 'select', 'label' => __( 'Peso de letra del menú', 'fan-deco-studio' ), 'default' => '500', 'choices' => array( '400' => 'Normal (400)', '500' => 'Media (500)', '600' => 'Seminegrita (600)', '700' => 'Negrita (700)' ), 'vars' => array( '--fd-menu-weight' => '{v}' ) ),
			),
		),
		'cards'    => array(
			'title'  => __( 'Tarjetas de producto', 'fan-deco-studio' ),
			'desc'   => __( 'Cómo se ven los productos en la tienda, la portada y las categorías.', 'fan-deco-studio' ),
			'fields' => array(
				'p_ratio'     => array( 'type' => 'select', 'label' => __( 'Proporción de la foto', 'fan-deco-studio' ), 'default' => '4-5', 'choices' => array( '1-1' => __( 'Cuadrada (1:1)', 'fan-deco-studio' ), '4-5' => __( 'Vertical suave (4:5, por defecto)', 'fan-deco-studio' ), '3-4' => __( 'Vertical (3:4)', 'fan-deco-studio' ), '2-3' => __( 'Muy vertical (2:3)', 'fan-deco-studio' ) ), 'map' => array( '1-1' => '1 / 1', '4-5' => '4 / 5', '3-4' => '3 / 4', '2-3' => '2 / 3' ), 'vars' => array( '--fd-card-ratio' => '{v}' ) ),
				'p_quickview' => array( 'type' => 'toggle', 'label' => __( 'Mostrar el botón "Vista rápida"', 'fan-deco-studio' ), 'default' => 1, 'class_off' => 'fd-hide-quickview' ),
				'p_badges'    => array( 'type' => 'toggle', 'label' => __( 'Mostrar etiquetas (Oferta, Destacado…)', 'fan-deco-studio' ), 'default' => 1, 'class_off' => 'fd-hide-badges' ),
				'p_dots'      => array( 'type' => 'toggle', 'label' => __( 'Mostrar puntos de colores sobre la foto', 'fan-deco-studio' ), 'default' => 1, 'class_off' => 'fd-hide-dots' ),
			),
		),
	);
	return $schema;
}

/** Campos en una sola lista: clave => definición (con la sección). */
function fandeco_design_fields(): array {
	$out = array();
	foreach ( fandeco_design_schema() as $sid => $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$out[ $key ] = $field + array( 'section' => $sid );
		}
	}
	return $out;
}

/** Normaliza un valor según su campo. Devuelve null si no es válido. */
function fandeco_design_normalize( array $field, $raw ) {
	switch ( $field['type'] ) {
		case 'color':
			$hex = sanitize_hex_color( (string) $raw );
			return $hex ? strtolower( $hex ) : ( '' === trim( (string) $raw ) ? '' : null );
		case 'range':
			if ( ! is_numeric( $raw ) ) {
				return null;
			}
			return (string) max( (float) $field['min'], min( (float) $field['max'], (float) $raw ) );
		case 'select':
			$raw = (string) $raw;
			return array_key_exists( $raw, $field['choices'] ) ? $raw : null;
		case 'toggle':
			return empty( $raw ) || '0' === (string) $raw ? '0' : '1';
	}
	return null;
}

/** Formatea un número con hasta 4 decimales, sin ceros sobrantes (igual que el JS de la vista previa). */
function fandeco_design_num( float $n ): string {
	$s = rtrim( rtrim( number_format( $n, 4, '.', '' ), '0' ), '.' );
	return '-0' === $s || '' === $s ? '0' : $s;
}

/** Valor efectivo (normalizado) de un campo: el guardado o, si no es válido, el de por defecto. */
function fandeco_design_get( string $key, ?array $values = null ) {
	$fields = fandeco_design_fields();
	$field  = $fields[ $key ];
	$raw    = null === $values ? get_theme_mod( 'fandeco_d_' . $key, $field['default'] ) : ( $values[ $key ] ?? $field['default'] );
	$norm   = fandeco_design_normalize( $field, $raw );
	return null === $norm ? fandeco_design_normalize( $field, $field['default'] ) : $norm;
}

/**
 * CSS de salida con SOLO los valores distintos del de por defecto.
 *
 * @param array<string,mixed>|null $values Valores por clave (null = los guardados del tema).
 */
function fandeco_design_build_css( ?array $values = null ): string {
	$desktop = array();
	$mobile  = array();
	foreach ( fandeco_design_fields() as $key => $field ) {
		if ( 'toggle' === $field['type'] ) {
			continue;
		}
		$val = fandeco_design_get( $key, $values );
		$def = fandeco_design_normalize( $field, $field['default'] );
		if ( $val === $def || '' === $val ) {
			continue;
		}
		if ( 'range' === $field['type'] ) {
			$num = (float) $val;
			if ( ( $field['emit'] ?? '' ) === 'ratio' ) {
				$num /= 100;
			}
			$out = fandeco_design_num( $num );
		} elseif ( 'select' === $field['type'] && isset( $field['map'] ) ) {
			$out = (string) $field['map'][ $val ];
		} else {
			$out = $val;
		}
		// El espacio lateral automático (0) no se emite.
		if ( 'l_gutter' === $key && (float) $val <= 0 ) {
			continue;
		}
		foreach ( $field['vars'] as $var => $tpl ) {
			$decl = $var . ':' . str_replace( '{v}', $out, $tpl );
			if ( ( $field['media'] ?? '' ) === 'mobile' ) {
				$mobile[] = $decl;
			} else {
				$desktop[] = $decl;
			}
		}
	}
	$css = '';
	if ( $desktop ) {
		$css .= ':root{' . implode( ';', $desktop ) . '}';
	}
	if ( $mobile ) {
		$css .= '@media (max-width:767px){:root{' . implode( ';', $mobile ) . '}}';
	}
	return $css;
}

/** Clases de <body> de los interruptores apagados. */
function fandeco_design_body_classes( ?array $values = null ): array {
	$classes = array();
	foreach ( fandeco_design_fields() as $key => $field ) {
		if ( 'toggle' === $field['type'] && '0' === fandeco_design_get( $key, $values ) ) {
			$classes[] = $field['class_off'];
		}
	}
	return $classes;
}

/** Esquema compacto para la vista previa en vivo (JS). */
function fandeco_design_js_schema(): array {
	$out = array();
	foreach ( fandeco_design_fields() as $key => $f ) {
		$out[ $key ] = array_filter(
			array(
				'type'      => $f['type'],
				'default'   => $f['default'],
				'min'       => $f['min'] ?? null,
				'max'       => $f['max'] ?? null,
				'choices'   => isset( $f['choices'] ) ? array_map( 'strval', array_keys( $f['choices'] ) ) : null, // PHP vuelve enteras las claves numéricas: se envían como texto
				'map'       => $f['map'] ?? null,
				'vars'      => $f['vars'] ?? null,
				'emit'      => $f['emit'] ?? null,
				'media'     => $f['media'] ?? null,
				'class_off' => $f['class_off'] ?? null,
			),
			static fn( $v ) => null !== $v
		);
	}
	return $out;
}

/* ---- Salida en el sitio ---- */
add_action(
	'wp_head',
	static function () {
		// Siempre se imprime la etiqueta (aunque esté vacía) para que la vista previa del Personalizador la actualice.
		echo '<style id="fd-design">' . fandeco_design_build_css() . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- valores validados (colores hex, números y claves de listas).
	},
	99
);

add_filter(
	'body_class',
	static function ( $classes ) {
		return array_merge( $classes, fandeco_design_body_classes() );
	}
);

/* ---- Personalizador ---- */
add_action(
	'customize_register',
	static function ( $wp_customize ) {
		$priority = 20;
		foreach ( fandeco_design_schema() as $sid => $section ) {
			$wp_customize->add_section(
				'fandeco_design_' . $sid,
				array(
					'title'       => $section['title'],
					'description' => $section['desc'],
					'panel'       => 'fandeco',
					'priority'    => $priority++,
				)
			);
			foreach ( $section['fields'] as $key => $field ) {
				$id    = 'fandeco_d_' . $key;
				$field = $field + array( 'section' => $sid );
				$wp_customize->add_setting(
					$id,
					array(
						'default'           => $field['default'],
						'transport'         => 'postMessage',
						'sanitize_callback' => static function ( $value ) use ( $field ) {
							$norm = fandeco_design_normalize( $field, $value );
							return null === $norm ? $field['default'] : $norm;
						},
					)
				);
				$common = array(
					'label'   => $field['label'],
					'section' => 'fandeco_design_' . $sid,
				);
				switch ( $field['type'] ) {
					case 'color':
						$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $common + array( 'settings' => $id ) ) );
						break;
					case 'range':
						$wp_customize->add_control(
							$id,
							$common + array(
								'type'        => 'range',
								'input_attrs' => array(
									'min'  => $field['min'],
									'max'  => $field['max'],
									'step' => $field['step'],
								),
							)
						);
						break;
					case 'select':
						$wp_customize->add_control( $id, $common + array( 'type' => 'select', 'choices' => $field['choices'] ) );
						break;
					case 'toggle':
						$wp_customize->add_control( $id, $common + array( 'type' => 'checkbox' ) );
						break;
				}
			}
		}
	},
	20
);

add_action(
	'customize_preview_init',
	static function () {
		wp_enqueue_script( 'fandeco-design-preview', FANDECO_URI . '/assets/js/design-preview.js', array( 'customize-preview' ), FANDECO_VERSION, true );
		wp_localize_script( 'fandeco-design-preview', 'fandecoDesign', array( 'schema' => fandeco_design_js_schema() ) );
	}
);
