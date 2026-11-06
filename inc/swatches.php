<?php
/**
 * Selectores visuales de variaciones (color / botones) SIN depender de Blocksy.
 *
 * Principio: el <select> original de WooCommerce se conserva (oculto) y sigue siendo la fuente de
 * verdad; los botones solo lo manipulan y disparan `change`, así el JS nativo de variaciones
 * (precio, stock, imagen, disponibilidad) funciona igual. No toca precios, descuentos ni envíos.
 *
 * Tipos de atributo global (Productos > Atributos): "color" -> círculo; "button" -> botón con texto;
 * otros -> selector estándar de WooCommerce (sin cambios). Los tipos los registraba Blocksy Companion;
 * si ese plugin se desactiva, el tipo guardado sigue en la base de datos y este código lo interpreta.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

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

/** Color de un término: 1) campo propio, 2) datos que dejó Blocksy (si existen), 3) nombre conocido. */
function fandeco_swatch_color( $term ): string {
	if ( ! $term ) {
		return '';
	}
	$own = get_term_meta( $term->term_id, 'fandeco_swatch_color', true );
	if ( is_string( $own ) && '' !== fandeco_find_hex( $own ) ) {
		return fandeco_find_hex( $own );
	}
	// Blocksy (Variation Swatches, Shop Extra) guarda el color en el meta del término; la clave exacta cambia entre versiones,
	// así que se revisan los metas del término cuyo nombre sugiere Blocksy, color o muestra, buscando el primer color hexadecimal.
	foreach ( (array) get_term_meta( $term->term_id ) as $meta_key => $values ) {
		if ( 'fandeco_swatch_color' === $meta_key || ! preg_match( '/blocksy|color|swatch/i', (string) $meta_key ) ) {
			continue;
		}
		foreach ( (array) $values as $raw ) {
			$found = fandeco_find_hex( is_string( $raw ) && is_serialized( $raw ) ? @unserialize( $raw, array( 'allowed_classes' => false ) ) : $raw ); // sin objetos: solo datos
			if ( '' !== $found ) {
				return $found;
			}
		}
	}
	$key   = sanitize_title( remove_accents( (string) $term->name ) );
	$names = fandeco_color_names();
	return isset( $names[ $key ] ) ? $names[ $key ] : '';
}

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

/** Reemplaza el desplegable de variaciones por botones, conservando el <select> original. */
add_filter(
	'woocommerce_dropdown_variation_attribute_options_html',
	static function ( string $html, array $args ): string {
		$attribute = (string) ( $args['attribute'] ?? '' );
		$product   = $args['product'] ?? null;
		if ( '' === $attribute || ! taxonomy_exists( $attribute ) ) {
			return $html; // atributos personalizados del producto (no globales): se dejan como están.
		}
		$type = fandeco_swatch_type( $attribute );
		if ( 'select' === $type ) {
			return $html;
		}
		$options = (array) ( $args['options'] ?? array() );
		if ( ! $options && $product instanceof WC_Product ) {
			$all     = $product->get_variation_attributes();
			$options = (array) ( $all[ $attribute ] ?? array() );
		}
		if ( ! $options ) {
			return $html;
		}
		$selected = (string) ( $args['selected'] ?? '' );
		$label    = function_exists( 'wc_attribute_label' ) ? wc_attribute_label( $attribute, $product ) : $attribute;
		$buttons  = '';
		$cur_name = '';
		foreach ( $options as $slug ) {
			$term = get_term_by( 'slug', $slug, $attribute );
			$name = $term ? (string) $term->name : (string) $slug;
			$on   = ( (string) $slug === $selected );
			if ( $on ) {
				$cur_name = $name;
			}
			$color = ( 'color' === $type ) ? fandeco_swatch_color( $term ) : '';
			if ( 'color' === $type && '' !== $color ) {
				$buttons .= sprintf(
					'<button type="button" class="fd-swatch fd-swatch--color" role="radio" aria-checked="%1$s" data-value="%2$s" data-name="%3$s" title="%3$s" style="--fd-swatch:%4$s" tabindex="%5$s"><span class="screen-reader-text">%3$s</span></button>',
					$on ? 'true' : 'false',
					esc_attr( $slug ),
					esc_attr( $name ),
					esc_attr( $color ),
					$on ? '0' : '-1'
				);
			} else { // tipo botón, o color sin valor conocido: botón con el nombre (siempre usable).
				$buttons .= sprintf(
					'<button type="button" class="fd-swatch fd-swatch--button" role="radio" aria-checked="%1$s" data-value="%2$s" data-name="%3$s" tabindex="%4$s">%3$s</button>',
					$on ? 'true' : 'false',
					esc_attr( $slug ),
					esc_html( $name ),
					$on ? '0' : '-1'
				);
			}
		}
		return sprintf(
			'<div class="fd-swatches-wrap"><span class="fd-swatch-source" hidden>%1$s</span><div class="fd-swatches" role="radiogroup" aria-label="%2$s" data-fd-swatches>%3$s</div><span class="fd-swatches__current" data-fd-swatch-current aria-live="polite">%4$s</span></div>',
			$html, // select original generado por WooCommerce (ya escapado por él).
			esc_attr( $label ),
			$buttons, // botones construidos arriba con escapado.
			esc_html( $cur_name )
		);
	},
	20,
	2
);

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
	// Foto de cada variación según el valor del primer atributo (la primera que tenga imagen propia).
	$images = array();
	foreach ( (array) $product->get_children() as $child_id ) {
		$val = (string) get_post_meta( (int) $child_id, 'attribute_' . $key, true );
		$img = (int) get_post_thumbnail_id( (int) $child_id );
		if ( '' !== $val && $img && ! isset( $images[ $val ] ) ) {
			$images[ $val ] = $img;
		}
	}
	foreach ( $first as $value ) {
		$term  = taxonomy_exists( $key ) ? get_term_by( 'slug', $value, $key ) : null;
		$out[] = array(
			'name'     => $term ? (string) $term->name : (string) $value,
			'color'    => ( $is_color && $term ) ? fandeco_swatch_color( $term ) : '',
			'image_id' => (int) ( $images[ $value ] ?? 0 ),
		);
	}
	return $out;
}

/* ---- Administración: campo de color en los términos de atributos de tipo "color" ---- */
add_action(
	'admin_init',
	static function () {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return;
		}
		foreach ( (array) wc_get_attribute_taxonomies() as $attr ) {
			$tax = wc_attribute_taxonomy_name( $attr->attribute_name );
			if ( 'color' !== fandeco_swatch_type( $tax ) ) {
				continue;
			}
			add_action( "{$tax}_add_form_fields", 'fandeco_swatch_field_add' );
			add_action( "{$tax}_edit_form_fields", 'fandeco_swatch_field_edit', 10, 1 );
			add_action( "created_{$tax}", 'fandeco_swatch_field_save' );
			add_action( "edited_{$tax}", 'fandeco_swatch_field_save' );
		}
	}
);

function fandeco_swatch_field_add(): void {
	wp_nonce_field( 'fandeco_swatch', 'fandeco_swatch_nonce' );
	echo '<div class="form-field"><label for="fandeco_swatch_color">' . esc_html__( 'Color del selector', 'fan-deco-studio' ) . '</label>';
	echo '<input type="color" id="fandeco_swatch_color" name="fandeco_swatch_color" value="#ffffff"> ';
	echo '<label><input type="checkbox" name="fandeco_swatch_on" value="1"> ' . esc_html__( 'Usar este color', 'fan-deco-studio' ) . '</label>';
	echo '<p>' . esc_html__( 'Si no se define, se intenta deducir del nombre; si no se reconoce, se muestra un botón con texto.', 'fan-deco-studio' ) . '</p></div>';
}

function fandeco_swatch_field_edit( $term ): void {
	$color = fandeco_swatch_color( $term );
	$own   = (string) get_term_meta( $term->term_id, 'fandeco_swatch_color', true );
	wp_nonce_field( 'fandeco_swatch', 'fandeco_swatch_nonce' );
	echo '<tr class="form-field"><th scope="row"><label for="fandeco_swatch_color">' . esc_html__( 'Color del selector', 'fan-deco-studio' ) . '</label></th><td>';
	printf( '<input type="color" id="fandeco_swatch_color" name="fandeco_swatch_color" value="%s"> ', esc_attr( '' !== $color && strlen( $color ) === 7 ? $color : '#ffffff' ) );
	printf( '<label><input type="checkbox" name="fandeco_swatch_on" value="1" %s> %s</label>', checked( '' !== $own, true, false ), esc_html__( 'Usar este color (si no, se deduce del nombre u otros datos)', 'fan-deco-studio' ) );
	if ( '' !== $color && '' === $own ) {
		echo '<p class="description">' . sprintf( /* translators: %s: color */ esc_html__( 'Color actual deducido: %s. Marcá la casilla para fijarlo.', 'fan-deco-studio' ), esc_html( $color ) ) . '</p>';
	}
	echo '</td></tr>';
}

function fandeco_swatch_field_save( int $term_id ): void {
	if ( ! isset( $_POST['fandeco_swatch_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fandeco_swatch_nonce'] ) ), 'fandeco_swatch' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	$color = isset( $_POST['fandeco_swatch_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['fandeco_swatch_color'] ) ) : '';
	if ( ! empty( $_POST['fandeco_swatch_on'] ) && $color ) {
		update_term_meta( $term_id, 'fandeco_swatch_color', $color );
	} else {
		delete_term_meta( $term_id, 'fandeco_swatch_color' );
	}
}
