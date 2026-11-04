<?php
/**
 * SEO: el tema NO compite con plugins SEO. Si AIOSEO (u otro) está activo, no imprime nada propio.
 * Sin plugin SEO activo imprime un mínimo razonable: meta description, Open Graph/Twitter y JSON-LD
 * (WebSite + Organization en portada, BreadcrumbList en contenido). El schema de Producto, el de
 * migas de pan de WooCommerce y los canonical los generan WooCommerce/WordPress; no se duplican.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

/** ¿Hay un plugin SEO que se ocupa de metadatos? */
function fandeco_seo_plugin_active(): bool {
	$active = defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' )
		|| defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	return (bool) apply_filters( 'fandeco_seo_plugin_active', $active );
}

/** Limpieza de <head> y scripts innecesarios (rendimiento, sin tocar SEO). */
add_action(
	'init',
	static function () {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
);

/** Descripción de la vista actual (texto plano, ≤ 160 caracteres). */
function fandeco_seo_description(): string {
	$text = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$text = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	} elseif ( is_front_page() || is_home() ) {
		$text = get_bloginfo( 'description' );
	}
	if ( '' === trim( wp_strip_all_tags( (string) $text ) ) ) {
		$text = get_bloginfo( 'description' );
	}
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) ) );
	return wp_html_excerpt( $text, 160, '…' );
}

/** Imagen representativa (URL) para redes sociales. */
function fandeco_seo_image(): string {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( null, 'fandeco-hero' );
		if ( $url ) {
			return $url;
		}
	}
	if ( is_tax( 'product_cat' ) ) {
		$thumb = (int) get_term_meta( get_queried_object_id(), 'thumbnail_id', true );
		if ( $thumb ) {
			return (string) wp_get_attachment_image_url( $thumb, 'fandeco-hero' );
		}
	}
	$logo = (int) get_theme_mod( 'custom_logo' );
	return $logo ? (string) wp_get_attachment_image_url( $logo, 'full' ) : '';
}

add_action(
	'wp_head',
	static function () {
		if ( fandeco_seo_plugin_active() || is_404() || is_search() ) {
			return;
		}
		$desc  = fandeco_seo_description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
		$image = fandeco_seo_image();
		$type  = is_singular( 'post' ) ? 'article' : ( is_singular( 'product' ) ? 'product' : 'website' );

		if ( $desc ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		if ( $desc ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		if ( $url ) {
			printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		}
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}
		printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	},
	5
);

/** JSON-LD de respaldo. */
add_action(
	'wp_head',
	static function () {
		if ( fandeco_seo_plugin_active() ) {
			return;
		}
		$graph = array();
		if ( is_front_page() ) {
			$logo    = (int) get_theme_mod( 'custom_logo' );
			$same_as = array_values( fandeco_social_links() );
			$org     = array(
				'@type' => 'Organization',
				'@id'   => home_url( '/#organization' ),
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			);
			if ( $logo ) {
				$org['logo'] = (string) wp_get_attachment_image_url( $logo, 'full' );
			}
			if ( $same_as ) {
				$org['sameAs'] = $same_as;
			}
			$email = (string) fandeco_option( 'email', '' );
			if ( $email ) {
				$org['email'] = $email;
			}
			$graph[] = $org;
			$site    = array(
				'@type'     => 'WebSite',
				'@id'       => home_url( '/#website' ),
				'url'       => home_url( '/' ),
				'name'      => get_bloginfo( 'name' ),
				'inLanguage' => str_replace( '_', '-', get_locale() ),
				'publisher' => array( '@id' => home_url( '/#organization' ) ),
			);
			if ( fandeco_has_woocommerce() ) {
				$site['potentialAction'] = array(
					'@type'       => 'SearchAction',
					'target'      => home_url( '/?s={search_term_string}&post_type=product' ),
					'query-input' => 'required name=search_term_string',
				);
			}
			$graph[] = $site;
		} elseif ( is_singular() && ! ( fandeco_has_woocommerce() && is_woocommerce() ) ) {
			// BreadcrumbList simple (Inicio > ancestros > actual). WooCommerce genera el suyo para tienda/producto.
			$items = array( array( 'name' => __( 'Inicio', 'fan-deco-studio' ), 'url' => home_url( '/' ) ) );
			foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
				$items[] = array( 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) );
			}
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
			$list    = array();
			foreach ( $items as $i => $item ) {
				$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $item['name'] ), 'item' => $item['url'] );
			}
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
		}
		if ( $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	6
);
