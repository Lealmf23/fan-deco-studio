<?php
/**
 * Carga de assets compilados con Vite (manifest) y modo desarrollo con HMR.
 *
 * Producción: lee dist/.vite/manifest.json.
 * Desarrollo: define en wp-config.php  define( 'FANDECO_VITE_DEV', true );  y ejecutá `npm run dev`.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

const FANDECO_VITE_ENTRY = 'src/js/main.js';

function fandeco_vite_dev_url(): string {
	return untrailingslashit( apply_filters( 'fandeco_vite_dev_url', 'http://localhost:5173' ) );
}

function fandeco_is_vite_dev(): bool {
	return defined( 'FANDECO_VITE_DEV' ) && FANDECO_VITE_DEV;
}

/** @return array<string,mixed> */
function fandeco_manifest(): array {
	static $manifest = null;
	if ( null === $manifest ) {
		$manifest = array();
		$file     = FANDECO_DIR . '/dist/.vite/manifest.json';
		if ( is_readable( $file ) ) {
			$data     = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$manifest = is_array( $data ) ? $data : array();
		}
	}
	return $manifest;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( fandeco_is_vite_dev() ) {
			$base = fandeco_vite_dev_url();
			wp_enqueue_script( 'fandeco-vite-client', $base . '/@vite/client', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
			wp_enqueue_script( 'fandeco-main', $base . '/' . FANDECO_VITE_ENTRY, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
			return;
		}

		$manifest = fandeco_manifest();
		$entry    = $manifest[ FANDECO_VITE_ENTRY ] ?? null;
		if ( ! $entry ) {
			return; // Sin build: el tema sigue funcionando, pero sin estilos. Ver README.
		}
		foreach ( (array) ( $entry['css'] ?? array() ) as $i => $css ) {
			wp_enqueue_style( 'fandeco-main-' . $i, FANDECO_URI . '/dist/' . $css, array(), FANDECO_VERSION );
		}
		wp_enqueue_script( 'fandeco-main', FANDECO_URI . '/dist/' . $entry['file'], array(), FANDECO_VERSION, true );
	}
);

/** Scripts de Vite como módulos ES. */
add_filter(
	'script_loader_tag',
	static function ( string $tag, string $handle, string $src ): string {
		if ( in_array( $handle, array( 'fandeco-main', 'fandeco-vite-client' ), true ) ) {
			return sprintf( '<script type="module" src="%s" id="%s-js"></script>' . "\n", esc_url( $src ), esc_attr( $handle ) );
		}
		return $tag;
	},
	10,
	3
);

/** Precarga de la fuente del cuerpo (solo producción, solo subset latino). */
add_action(
	'wp_head',
	static function () {
		if ( fandeco_is_vite_dev() ) {
			return;
		}
		foreach ( fandeco_manifest() as $key => $item ) {
			if ( isset( $item['file'] ) && str_contains( $key, 'bricolage-grotesque-latin-wght-normal' ) ) {
				printf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( FANDECO_URI . '/dist/' . $item['file'] )
				);
			}
		}
	},
	1
);

/** El CSS compilado también se usa dentro del editor de bloques (iframe) para que la vista previa sea fiel. */
add_action(
	'enqueue_block_assets',
	static function () {
		if ( ! is_admin() || fandeco_is_vite_dev() ) {
			return;
		}
		$entry = fandeco_manifest()[ FANDECO_VITE_ENTRY ] ?? null;
		foreach ( (array) ( $entry['css'] ?? array() ) as $i => $css ) {
			wp_enqueue_style( 'fandeco-editor-' . $i, FANDECO_URI . '/dist/' . $css, array(), FANDECO_VERSION );
		}
	}
);
