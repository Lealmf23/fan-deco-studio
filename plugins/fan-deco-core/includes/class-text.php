<?php
/**
 * Utilidades de texto SIN dependencias de WordPress (se prueban con scripts/smoke/core-test.php).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || defined( 'FANDECO_CORE_TEST' ) || exit;

final class FanDeco_Text {

	/** Texto plano, espacios colapsados, recortado en palabra con “…”. */
	public static function truncate( string $text, int $max = 160 ): string {
		$text = html_entity_decode( strip_tags( $text ), ENT_QUOTES, 'UTF-8' );
		$text = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
		if ( mb_strlen( $text ) <= $max ) {
			return $text;
		}
		$cut   = mb_substr( $text, 0, $max - 1 );
		$space = mb_strrpos( $cut, ' ' );
		if ( false !== $space && $space > (int) ( $max * 0.6 ) ) {
			$cut = mb_substr( $cut, 0, $space );
		}
		return rtrim( $cut, " ,.;:-–—" ) . '…';
	}

	/** Reemplaza %variables% por valores; las desconocidas se eliminan; se limpian separadores sobrantes. */
	public static function replace_vars( string $template, array $vars ): string {
		$out = (string) preg_replace_callback(
			'/%([a-z_]+)%/',
			static fn( array $m ): string => (string) ( $vars[ $m[1] ] ?? '' ),
			$template
		);
		$out = trim( (string) preg_replace( '/\s+/u', ' ', $out ) );
		$sep = trim( (string) ( $vars['sep'] ?? '' ) );
		if ( '' !== $sep ) {
			$q   = preg_quote( $sep, '/' );
			$out = (string) preg_replace( '/(?:\s*' . $q . '\s*){2,}/u', ' ' . $sep . ' ', $out ); // separadores dobles.
			$out = trim( (string) preg_replace( '/^(?:' . $q . '\s*)+|(?:\s*' . $q . ')+$/u', '', $out ) ); // sobrantes en los bordes.
		}
		return $out;
	}

	/**
	 * Convierte plantillas de AIOSEO (#post_title, #separator_sa, …) a variables propias.
	 *
	 * @return array{0:string,1:string[]} [plantilla convertida, etiquetas no soportadas descartadas]
	 */
	public static function from_aioseo( string $template ): array {
		$map     = array(
			'#post_title'      => '%title%',
			'#page_title'      => '%title%',
			'#taxonomy_title'  => '%term%',
			'#category_title'  => '%term%',
			'#archive_title'   => '%title%',
			'#site_title'      => '%sitename%',
			'#tagline'         => '%tagline%',
			'#separator_sa'    => '%sep%',
			'#separator'       => '%sep%',
			'#current_page'    => '%page%',
		);
		$unknown = array();
		$out     = $template;
		// Más largas primero para no pisar prefijos (#separator_sa antes que #separator).
		uksort( $map, static fn( $a, $b ) => strlen( $b ) <=> strlen( $a ) );
		foreach ( $map as $tag => $var ) {
			$out = str_replace( $tag, $var, $out );
		}
		$out = (string) preg_replace_callback(
			'/#[a-z_]+(?:-\w+)?/',
			static function ( array $m ) use ( &$unknown ): string {
				$unknown[] = $m[0];
				return '';
			},
			$out
		);
		return array( trim( (string) preg_replace( '/\s+/u', ' ', $out ) ), array_values( array_unique( $unknown ) ) );
	}
}
