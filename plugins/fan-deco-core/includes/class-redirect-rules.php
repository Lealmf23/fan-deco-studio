<?php
/**
 * Lógica pura de reglas de redirección (sin WordPress; testeable).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || defined( 'FANDECO_CORE_TEST' ) || exit;

final class FanDeco_Redirect_Rules {

	/** Ruta normalizada: sin query, minúsculas, sin barra final (salvo raíz). */
	public static function normalize_path( string $uri ): string {
		$path = (string) parse_url( $uri, PHP_URL_PATH );
		$path = rawurldecode( $path );
		$path = '/' . ltrim( $path, '/' );
		if ( '/' !== $path ) {
			$path = rtrim( $path, '/' );
		}
		return mb_strtolower( $path );
	}

	/**
	 * Formato por línea:  /origen  ->  /destino  [301|302|307|308]
	 * Origen: ruta local. Destino: ruta local o URL del mismo sitio (se valida al aplicar).
	 *
	 * @return array{rules:array<int,array{from:string,to:string,code:int}>,errors:string[]}
	 */
	public static function parse( string $text ): array {
		$rules  = array();
		$errors = array();
		foreach ( preg_split( '/\R/u', $text ) ?: array() as $n => $line ) {
			$line = trim( $line );
			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}
			if ( ! preg_match( '/^(\S+)\s*(?:->|=>|→)\s*(\S+)(?:\s+(30[1278]))?$/u', $line, $m ) ) {
				$errors[] = 'Línea ' . ( $n + 1 ) . ': formato inválido.';
				continue;
			}
			if ( ! str_starts_with( $m[1], '/' ) ) {
				$errors[] = 'Línea ' . ( $n + 1 ) . ': el origen debe empezar con "/".';
				continue;
			}
			$from = self::normalize_path( $m[1] );
			if ( self::normalize_path( $m[2] ) === $from && ! preg_match( '#^https?://#i', $m[2] ) ) {
				$errors[] = 'Línea ' . ( $n + 1 ) . ': origen y destino son iguales (bucle).';
				continue;
			}
			$rules[ $from ] = array( 'from' => $from, 'to' => $m[2], 'code' => isset( $m[3] ) ? (int) $m[3] : 301 );
		}
		return array( 'rules' => array_values( $rules ), 'errors' => $errors );
	}

	/** @param array<int,array{from:string,to:string,code:int}> $rules */
	public static function match( array $rules, string $request_uri ): ?array {
		$path = self::normalize_path( $request_uri );
		foreach ( $rules as $rule ) {
			if ( $rule['from'] === $path ) {
				return $rule;
			}
		}
		return null;
	}

	/** Serializa reglas al formato de texto del editor. */
	public static function to_text( array $rules ): string {
		return implode( "\n", array_map( static fn( $r ) => "{$r['from']} -> {$r['to']} {$r['code']}", $rules ) );
	}
}
