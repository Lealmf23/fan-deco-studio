<?php
/**
 * Sitemaps: se usa el sitemap nativo de WordPress (/wp-sitemap.xml) y se ajusta para excluir lo no indexable.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Sitemaps {

	public static function init(): void {
		if ( FanDeco_Frontend::disabled() ) {
			return;
		}
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'exclude_noindex' ) );
		add_filter( 'wp_sitemaps_taxonomies', array( __CLASS__, 'taxonomies' ) );
		add_filter( 'wp_sitemaps_add_provider', array( __CLASS__, 'providers' ), 10, 2 );
		add_filter( 'robots_txt', array( __CLASS__, 'robots_txt' ), 20 );
	}

	public static function exclude_noindex( array $args ): array {
		$args['meta_query'] = array_merge(
			isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array(),
			array(
				array(
					'relation' => 'OR',
					array( 'key' => FanDeco_Meta::key( 'noindex' ), 'compare' => 'NOT EXISTS' ),
					array( 'key' => FanDeco_Meta::key( 'noindex' ), 'value' => '1', 'compare' => '!=' ),
				),
			)
		);
		return $args;
	}

	public static function taxonomies( array $taxes ): array {
		if ( FanDeco_Settings::get( 'noindex_tags' ) ) {
			unset( $taxes['post_tag'], $taxes['product_tag'] );
		}
		return $taxes;
	}

	public static function providers( $provider, string $name ) {
		if ( 'users' === $name && FanDeco_Settings::get( 'noindex_author' ) ) {
			return false;
		}
		return $provider;
	}

	/** Solo aplica si no existe un robots.txt físico en el servidor. */
	public static function robots_txt( string $output ): string {
		$extra = "Disallow: /*?add-to-cart=\nDisallow: /*?s=\nDisallow: /?s=\n";
		// Las reglas deben quedar dentro del grupo "User-agent: *", antes de las líneas Sitemap.
		$pos = stripos( $output, "\nSitemap:" );
		if ( false === $pos ) {
			return rtrim( $output ) . "\n" . $extra;
		}
		return substr( $output, 0, $pos + 1 ) . $extra . substr( $output, $pos + 1 );
	}
}
