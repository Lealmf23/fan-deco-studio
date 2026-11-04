<?php
/**
 * Salida SEO en el frontend: título, descripción, robots, canonical, Open Graph/Twitter y JSON-LD.
 * Se desactiva si hay otro plugin SEO activo (no duplicar). El schema de Producto lo genera WooCommerce.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Frontend {

	/** ¿Otro plugin SEO manda? */
	public static function disabled(): bool {
		$other = defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
		return (bool) apply_filters( 'fandeco_core_frontend_disabled', $other );
	}

	public static function init(): void {
		if ( self::disabled() ) {
			return;
		}
		add_filter( 'pre_get_document_title', array( __CLASS__, 'title' ), 20 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		remove_action( 'wp_head', 'rel_canonical' ); // imprimimos el nuestro (también para archivos/categorías).
	}

	/** ¿Qué es la vista actual? */
	private static function context(): array {
		$ctx = array( 'kind' => 'other', 'id' => 0, 'type' => '' );
		if ( is_singular() ) {
			$ctx = array( 'kind' => 'post', 'id' => (int) get_queried_object_id(), 'type' => (string) get_post_type() );
		} elseif ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) {
			$ctx = array( 'kind' => 'post', 'id' => (int) wc_get_page_id( 'shop' ), 'type' => 'page' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$ctx  = array( 'kind' => 'term', 'id' => $term instanceof WP_Term ? (int) $term->term_id : 0, 'type' => $term instanceof WP_Term ? $term->taxonomy : '' );
		} elseif ( is_home() || is_front_page() ) {
			$ctx['kind'] = 'home';
		} elseif ( is_search() ) {
			$ctx['kind'] = 'search';
		} elseif ( is_404() ) {
			$ctx['kind'] = '404';
		} elseif ( is_author() ) {
			$ctx['kind'] = 'author';
		} elseif ( is_archive() ) {
			$ctx['kind'] = 'archive';
		}
		return $ctx;
	}

	private static function meta( array $ctx, string $field ): string {
		if ( ! $ctx['id'] ) {
			return '';
		}
		$key = FanDeco_Meta::key( $field );
		$val = 'term' === $ctx['kind'] ? get_term_meta( $ctx['id'], $key, true ) : get_post_meta( $ctx['id'], $key, true );
		return is_string( $val ) ? $val : '';
	}

	private static function vars( array $ctx ): array {
		$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$title = '';
		if ( 'post' === $ctx['kind'] ) {
			$title = get_the_title( $ctx['id'] );
		} elseif ( 'term' === $ctx['kind'] ) {
			$term  = get_queried_object();
			$title = $term instanceof WP_Term ? $term->name : '';
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_archive() ) {
			$title = wp_strip_all_tags( get_the_archive_title() );
		}
		return array(
			'title'    => wp_strip_all_tags( html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ) ),
			'term'     => wp_strip_all_tags( html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ) ),
			'sitename' => get_bloginfo( 'name' ),
			'tagline'  => get_bloginfo( 'description' ),
			'sep'      => (string) FanDeco_Settings::get( 'sep' ),
			'page'     => $paged > 1 ? sprintf( 'Página %d', $paged ) : '',
		);
	}

	public static function title( $title ) {
		$ctx = self::context();
		if ( in_array( $ctx['kind'], array( 'search', '404' ), true ) ) {
			return $title; // WordPress ya da un título correcto para estas vistas.
		}
		$custom = self::meta( $ctx, 'title' );
		$vars   = self::vars( $ctx );
		if ( '' !== $custom ) {
			$out = FanDeco_Text::replace_vars( $custom, $vars );
		} else {
			$tpl = 'title_archive';
			if ( 'home' === $ctx['kind'] || is_front_page() ) {
				$tpl = 'title_home';
			} elseif ( 'term' === $ctx['kind'] ) {
				$tpl = 'title_term';
			} elseif ( 'post' === $ctx['kind'] ) {
				$tpl = 'product' === $ctx['type'] ? 'title_product' : ( 'page' === $ctx['type'] ? 'title_page' : 'title_post' );
			}
			$out = FanDeco_Text::replace_vars( (string) FanDeco_Settings::get( $tpl ), $vars );
		}
		if ( '' === $out ) {
			return $title;
		}
		if ( '' !== $vars['page'] && ! str_contains( $out, $vars['page'] ) ) {
			$out .= ' ' . $vars['sep'] . ' ' . $vars['page'];
		}
		return $out;
	}

	private static function description( array $ctx ): string {
		$custom = self::meta( $ctx, 'description' );
		if ( '' !== $custom ) {
			return FanDeco_Text::truncate( $custom, 200 );
		}
		$text = '';
		if ( 'post' === $ctx['kind'] ) {
			if ( 'product' === $ctx['type'] && function_exists( 'wc_get_product' ) ) {
				$p    = wc_get_product( $ctx['id'] );
				$text = $p ? ( $p->get_short_description() ?: $p->get_description() ) : '';
			}
			if ( '' === trim( wp_strip_all_tags( (string) $text ) ) ) {
				$post = get_post( $ctx['id'] );
				$text = $post ? ( $post->post_excerpt ?: excerpt_remove_blocks( strip_shortcodes( $post->post_content ) ) ) : '';
			}
		} elseif ( 'term' === $ctx['kind'] ) {
			$text = term_description( $ctx['id'] );
		}
		if ( '' === trim( wp_strip_all_tags( (string) $text ) ) && is_front_page() ) {
			$text = (string) FanDeco_Settings::get( 'desc_home' );
		}
		if ( '' === trim( wp_strip_all_tags( (string) $text ) ) ) {
			$text = (string) FanDeco_Settings::get( 'desc_home' ) ?: get_bloginfo( 'description' );
		}
		return FanDeco_Text::truncate( (string) $text, 160 );
	}

	public static function robots( array $robots ): array {
		$ctx  = self::context();
		$bad  = in_array( $ctx['kind'], array( 'search', '404' ), true );
		$bad  = $bad || ( 'author' === $ctx['kind'] && FanDeco_Settings::get( 'noindex_author' ) );
		$bad  = $bad || ( is_attachment() && FanDeco_Settings::get( 'noindex_attach' ) );
		$bad  = $bad || ( 'term' === $ctx['kind'] && in_array( $ctx['type'], array( 'post_tag', 'product_tag' ), true ) && FanDeco_Settings::get( 'noindex_tags' ) );
		$bad  = $bad || ( '1' === self::meta( $ctx, 'noindex' ) );
		$wc   = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || ( function_exists( 'is_account_page' ) && is_account_page() ) );
		$follow_off = '1' === self::meta( $ctx, 'nofollow' ) || $wc;
		if ( $bad || $wc ) {
			unset( $robots['index'] );
			$robots['noindex'] = true;
		}
		if ( $follow_off ) {
			unset( $robots['follow'] );
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	private static function canonical( array $ctx ): string {
		$custom = self::meta( $ctx, 'canonical' );
		if ( '' !== $custom ) {
			return $custom;
		}
		$paged = max( (int) get_query_var( 'paged' ), 1 );
		if ( 'post' === $ctx['kind'] ) {
			return (string) get_permalink( $ctx['id'] );
		}
		if ( 'term' === $ctx['kind'] ) {
			$link = get_term_link( (int) $ctx['id'], $ctx['type'] );
			return is_wp_error( $link ) ? '' : ( $paged > 1 ? get_pagenum_link( $paged, false ) : $link );
		}
		if ( 'home' === $ctx['kind'] ) {
			return home_url( '/' );
		}
		if ( in_array( $ctx['kind'], array( 'archive', 'author' ), true ) ) {
			return (string) get_pagenum_link( $paged, false );
		}
		return '';
	}

	private static function image( array $ctx ): string {
		$img = self::meta( $ctx, 'og_image' );
		if ( '' === $img && 'post' === $ctx['kind'] && has_post_thumbnail( $ctx['id'] ) ) {
			$img = (string) get_the_post_thumbnail_url( $ctx['id'], 'large' );
		}
		if ( '' === $img && 'term' === $ctx['kind'] ) {
			$thumb = (int) get_term_meta( $ctx['id'], 'thumbnail_id', true );
			$img   = $thumb ? (string) wp_get_attachment_image_url( $thumb, 'large' ) : '';
		}
		if ( '' === $img ) {
			$img = (string) FanDeco_Settings::get( 'og_image' );
		}
		return '' !== $img ? $img : self::logo();
	}

	private static function logo(): string {
		$logo = (string) FanDeco_Settings::get( 'org_logo' );
		if ( '' === $logo ) {
			$id   = (int) get_theme_mod( 'custom_logo' );
			$logo = $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
		}
		return $logo;
	}

	public static function head(): void {
		$ctx   = self::context();
		$desc  = in_array( $ctx['kind'], array( '404', 'search' ), true ) ? '' : self::description( $ctx );
		$canon = self::canonical( $ctx );
		$title = wp_get_document_title();
		$image = self::image( $ctx );
		$type  = 'post' === $ctx['kind'] && 'post' === $ctx['type'] ? 'article' : ( 'post' === $ctx['kind'] && 'product' === $ctx['type'] ? 'product' : 'website' );

		if ( $desc ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		if ( $canon ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canon ) );
		}
		printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		if ( $desc ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		if ( $canon ) {
			printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canon ) );
		}
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}
		printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );

		$verify = array(
			'google-site-verification' => 'verify_google',
			'msvalidate.01'            => 'verify_bing',
			'p:domain_verify'          => 'verify_pinterest',
			'facebook-domain-verification' => 'verify_facebook',
		);
		foreach ( $verify as $name => $key ) {
			$code = (string) FanDeco_Settings::get( $key );
			if ( '' !== $code ) {
				printf( '<meta name="%s" content="%s">' . "\n", esc_attr( $name ), esc_attr( $code ) );
			}
		}
		self::schema( $ctx, $canon, $title, $desc );
	}

	private static function schema( array $ctx, string $canon, string $title, string $desc ): void {
		if ( in_array( $ctx['kind'], array( '404', 'search' ), true ) ) {
			return;
		}
		$org_id  = home_url( '/#organization' );
		$site_id = home_url( '/#website' );
		$org     = array(
			'@type' => 'Organization',
			'@id'   => $org_id,
			'name'  => (string) ( FanDeco_Settings::get( 'org_name' ) ?: get_bloginfo( 'name' ) ),
			'url'   => home_url( '/' ),
		);
		if ( self::logo() ) {
			$org['logo'] = self::logo();
		}
		$social = array_values( array_filter( array_map( 'trim', explode( "\n", (string) FanDeco_Settings::get( 'social' ) ) ) ) );
		if ( $social ) {
			$org['sameAs'] = $social;
		}
		$contact = array();
		if ( FanDeco_Settings::get( 'org_email' ) ) {
			$contact['email'] = (string) FanDeco_Settings::get( 'org_email' );
		}
		if ( FanDeco_Settings::get( 'org_phone' ) ) {
			$contact['telephone'] = (string) FanDeco_Settings::get( 'org_phone' );
		}
		if ( $contact ) {
			$org['contactPoint'] = array_merge( array( '@type' => 'ContactPoint', 'contactType' => 'customer service', 'areaServed' => 'AR', 'availableLanguage' => 'es' ), $contact );
		}
		$graph = array( $org );

		$site = array( '@type' => 'WebSite', '@id' => $site_id, 'url' => home_url( '/' ), 'name' => get_bloginfo( 'name' ), 'inLanguage' => str_replace( '_', '-', get_locale() ), 'publisher' => array( '@id' => $org_id ) );
		if ( function_exists( 'WC' ) ) {
			$site['potentialAction'] = array( '@type' => 'SearchAction', 'target' => home_url( '/?s={search_term_string}&post_type=product' ), 'query-input' => 'required name=search_term_string' );
		}
		$graph[] = $site;

		if ( $canon ) {
			$page = array( '@type' => 'term' === $ctx['kind'] ? 'CollectionPage' : 'WebPage', '@id' => $canon . '#webpage', 'url' => $canon, 'name' => $title, 'isPartOf' => array( '@id' => $site_id ), 'inLanguage' => str_replace( '_', '-', get_locale() ) );
			if ( $desc ) {
				$page['description'] = $desc;
			}
			$graph[] = $page;
		}

		// Migas: WooCommerce ya imprime su BreadcrumbList en tienda/producto/categorías → solo para el resto.
		$wc_page = function_exists( 'is_woocommerce' ) && is_woocommerce();
		if ( 'post' === $ctx['kind'] && ! is_front_page() && ! $wc_page ) {
			$items = array( array( 'Inicio', home_url( '/' ) ) );
			foreach ( array_reverse( get_post_ancestors( $ctx['id'] ) ) as $a ) {
				$items[] = array( get_the_title( $a ), get_permalink( $a ) );
			}
			$items[] = array( get_the_title( $ctx['id'] ), get_permalink( $ctx['id'] ) );
			$list    = array();
			foreach ( $items as $i => $it ) {
				$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $it[0] ), 'item' => $it[1] );
			}
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
		}

		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
