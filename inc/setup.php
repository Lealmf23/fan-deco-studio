<?php
/**
 * Soportes del tema, tamaños de imagen y ajustes base.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'fan-deco-studio', FANDECO_DIR . '/languages' );

		add_theme_support( 'title-tag' ); // AIOSEO se encarga del <title> si está activo.
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 120,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Tamaños pensados para hero, tarjetas y categorías.
		add_image_size( 'fandeco-hero', 1920, 1280, false );
		add_image_size( 'fandeco-hero-mobile', 900, 1100, false );
		add_image_size( 'fandeco-card', 800, 800, true );
		add_image_size( 'fandeco-wide', 1400, 900, true );
	}
);

add_action(
	'after_setup_theme',
	static function () {
		$GLOBALS['content_width'] = 1200;
	},
	0
);

/** Clase `no-js`/`js` para progresividad y para ocultar elementos animables solo si hay JS y no hay reduced-motion. */
add_action(
	'wp_head',
	static function () {
		?>
<script>document.documentElement.classList.add('js');if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.classList.add('fd-anim');}</script>
		<?php
	},
	0
);
