<?php
/**
 * Parseo PURO de los campos de "Ficha de producto" (sin WordPress; testeable).
 * Formato de texto simple, una entrada por línea.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || defined( 'FANDECO_CORE_TEST' ) || exit;

final class FanDeco_Pinfo_Parse {

	public const ICONS = array( 'check', 'shield', 'truck', 'return', 'star', 'leaf', 'card', 'lock', 'gift', 'tag', 'clock', 'pin' );
	public const TONES = array( 'neutral', 'green', 'wine' );

	private static function clean( string $s, int $max ): string {
		$s = trim( (string) preg_replace( '/\s+/u', ' ', strip_tags( $s ) ) );
		return mb_strlen( $s ) > $max ? mb_substr( $s, 0, $max ) : $s;
	}

	/** @return string[] líneas no vacías (máx. $limit) */
	public static function lines( string $text, int $limit = 10, int $max = 160 ): array {
		$out = array();
		foreach ( preg_split( '/\R/u', $text ) ?: array() as $line ) {
			$line = self::clean( $line, $max );
			if ( '' !== $line ) {
				$out[] = $line;
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}

	/** "Etiqueta | Valor" (también acepta "Etiqueta: Valor"). @return array<int,array{0:string,1:string}> */
	public static function specs( string $text, int $limit = 20 ): array {
		$rows = array();
		foreach ( preg_split( '/\R/u', $text ) ?: array() as $line ) {
			$sep = str_contains( $line, '|' ) ? '|' : ( str_contains( $line, ':' ) ? ':' : '' );
			if ( '' === $sep ) {
				continue;
			}
			[ $label, $value ] = array_pad( explode( $sep, $line, 2 ), 2, '' );
			$label = self::clean( $label, 60 );
			$value = self::clean( $value, 200 );
			if ( '' !== $label && '' !== $value ) {
				$rows[] = array( $label, $value );
			}
			if ( count( $rows ) >= $limit ) {
				break;
			}
		}
		return $rows;
	}

	/** "icono | texto". Icono desconocido -> check. @return array<int,array{icon:string,text:string}> */
	public static function trust( string $text, int $limit = 6 ): array {
		$out = array();
		foreach ( preg_split( '/\R/u', $text ) ?: array() as $line ) {
			if ( ! str_contains( $line, '|' ) ) {
				$icon = 'check';
				$body = $line;
			} else {
				[ $icon, $body ] = array_map( 'trim', explode( '|', $line, 2 ) );
				$icon = in_array( strtolower( $icon ), self::ICONS, true ) ? strtolower( $icon ) : 'check';
			}
			$body = self::clean( $body, 120 );
			if ( '' !== $body ) {
				$out[] = array( 'icon' => $icon, 'text' => $body );
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}

	/** "icono | título | texto | tono". @return array<int,array{icon:string,title:string,text:string,tone:string}> */
	public static function cards( string $text, int $limit = 4 ): array {
		$out = array();
		foreach ( preg_split( '/\R/u', $text ) ?: array() as $line ) {
			$p     = array_pad( array_map( 'trim', explode( '|', $line, 4 ) ), 4, '' );
			$title = self::clean( $p[1], 60 );
			if ( '' === $title ) {
				continue;
			}
			$out[] = array(
				'icon'  => in_array( strtolower( $p[0] ), self::ICONS, true ) ? strtolower( $p[0] ) : 'check',
				'title' => $title,
				'text'  => self::clean( $p[2], 160 ),
				'tone'  => in_array( strtolower( $p[3] ), self::TONES, true ) ? strtolower( $p[3] ) : 'neutral',
			);
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}
}
