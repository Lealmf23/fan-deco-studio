<?php
/**
 * Prueba del plugin fan-deco-blocks SIN ningún archivo del tema: carga solo sus funciones de apoyo y renderiza todos los bloques.
 */
define( 'FD_PLUGIN_ONLY', true );
function get_template_part( $slug, $name = null, $args = array() ) { $args['no_quickview'] = true; include FANDECO_DIR . '/template-parts/product-card.php'; }
require __DIR__ . '/bootstrap.php';
require FANDECO_DIR . '/includes/theme-shared.php';

$fail = 0;
function check( bool $ok, string $label ) { global $fail; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $label . "\n"; if ( ! $ok ) { $fail++; } }

echo "Plugin Fan Deco Blocks (sin el tema)\n";
$needed = array( 'fandeco_has_woocommerce', 'fandeco_enum', 'fandeco_icon_choices', 'fandeco_icon', 'fandeco_attachment_image', 'fandeco_picture', 'fandeco_button', 'fandeco_block_attrs', 'fandeco_title_html', 'fandeco_product_badges', 'fandeco_loop_add_to_cart_html', 'fandeco_variation_swatch_data' );
check( ! array_filter( $needed, static fn( $f ) => ! function_exists( $f ) ), 'funciones de apoyo definidas por el plugin (generadas desde el tema)' );
check( ! function_exists( 'fandeco_menu_fallback' ) && ! function_exists( 'fandeco_design_build_css' ), 'el plugin no arrastra funciones del tema que no necesita' );

$blocks = glob( FANDECO_DIR . '/build/blocks/*/block.json' ) ?: array();
check( count( $blocks ) === count( glob( dirname( __DIR__, 2 ) . '/src/blocks/*/block.json' ) ), 'build/blocks tiene todos los bloques (' . count( $blocks ) . ')' );

$bad = array();
foreach ( $blocks as $bj ) {
	$name = basename( dirname( $bj ) );
	try {
		$html = fd_render_block( $name, in_array( $name, array( 'banner-carousel', 'hero' ), true ) ? array( 'title' => '**Hola** *mundo*' ) : array() );
		if ( false !== strpos( $html, 'Fatal' ) || false !== strpos( $html, 'Warning' ) ) { $bad[] = $name; }
	} catch ( Throwable $e ) {
		$bad[] = $name . ': ' . $e->getMessage();
	}
}
check( ! $bad, 'todos los bloques se dibujan sin errores con las funciones del plugin' . ( $bad ? ' - ' . implode( '; ', $bad ) : '' ) );

$fp = fd_render_block( 'featured-products', array( 'layout' => 'editorial' ) );
check( str_contains( $fp, 'fd-product-card' ) && ! str_contains( $fp, 'fd-quickview' ), 'productos destacados: usa la tarjeta del plugin y sin vista rápida (ya la trae Blocksy)' );
check( str_contains( fd_render_block( 'marquee', array() ), 'fd-marquee__track' ), 'franja de mensajes dibujada' );

ob_start(); include FANDECO_DIR . '/patterns/home.php'; $pattern = (string) ob_get_clean();
check( str_contains( $pattern, 'wp:fandeco/banner-carousel' ) && str_contains( $pattern, 'wp:fandeco/marquee' ) && str_contains( $pattern, 'wp:fandeco/category-grid' ), 'patrón de portada: se genera con los bloques nuevos' );

foreach ( array( 'dist/blocks.css', 'dist/blocks.js', 'patterns/home.php', 'template-parts/product-card.php' ) as $f ) {
	if ( ! is_file( FANDECO_DIR . '/' . $f ) ) { check( false, "falta $f" ); }
}
$css = (string) file_get_contents( FANDECO_DIR . '/dist/blocks.css' );
check( str_contains( $css, '.fd-bancar' ) && str_contains( $css, '.fd-marquee' ) && ! str_contains( $css, '.fd-header' ) && ! str_contains( $css, '.fd-footer' ) && ! preg_match( '/(^|\})\s*(html|body)\s*[,{]/', $css ), 'CSS del plugin: trae los bloques y NO toca header, footer, html ni body del otro tema' );
check( strlen( $css ) < 80000, 'CSS del plugin liviano (' . round( strlen( $css ) / 1024 ) . ' kB)' );

$rel = (string) file_get_contents( dirname( __DIR__, 2 ) . '/plugins/fan-deco-blocks/includes/related.php' );
check( str_contains( $rel, 'woocommerce_after_single_product_summary' ) && str_contains( $rel, "'woocommerce_related_products'" ) && str_contains( $rel, 'fandeco/featured-products' ) && str_contains( $rel, 'fandeco_blocks_related' ), 'relacionados en la ficha: reemplazan a los del tema con las tarjetas de Fan Deco y se pueden apagar con un filtro' );
check( str_contains( (string) file_get_contents( dirname( __DIR__, 2 ) . '/plugins/fan-deco-blocks/fan-deco-blocks.php' ), 'includes/related.php' ), 'el plugin carga los relacionados' );

$pc = fd_render_block( 'photo-carousel', array( 'photos' => array( array( 'imageId' => 1, 'alt' => 'Uno', 'caption' => 'Lámpara <b>Aura</b>', 'url' => '/producto/aura' ), array( 'imageId' => 2 ), array( 'imageId' => 0 ), array( 'imageId' => 999 ) ), 'perView' => 9, 'ratio' => '16/9' ) );
check( 2 === substr_count( $pc, 'class="fd-photo-item"' ) && str_contains( $pc, 'data-fd-carousel-prev' ) && str_contains( $pc, '--fd-per:5' ) && str_contains( $pc, '--fd-photo-ratio:16 / 9' ), 'carrusel de fotos: descarta fotos inválidas, limita fotos por vista y trae flechas' );
check( str_contains( $pc, 'href="/producto/aura"' ) && ! str_contains( $pc, '<b>' ) && str_contains( $pc, 'fd-photo__cap' ), 'carrusel de fotos: enlace y texto saneado' );
check( ! str_contains( fd_render_block( 'photo-carousel', array( 'photos' => array() ) ), '<section' ), 'carrusel de fotos: sin fotos no imprime nada' );
check( ! str_contains( fd_render_block( 'photo-carousel', array( 'photos' => array( array( 'imageId' => 1 ) ), 'ratio' => 'x" onload="1' ) ), 'onload' ), 'carrusel de fotos: proporción fuera de la lista se descarta' );
$GLOBALS['fd_img'] = array_fill_keys( range( 1, 9 ), '/img/x.jpg' );
$fpc = fd_render_block( 'featured-products', array( 'layout' => 'grid' ) );
check( str_contains( $fpc, 'data-src=' ) && ! preg_match( '/<img[^>]*fd-product-card__alt[^>]*\ssrc=/', $fpc ), 'tarjetas: la segunda foto no se descarga hasta pasar el mouse' );

$sh = fd_render_block( 'section-heading', array( 'title' => 'Hola', 'titleSize' => 500, 'descSize' => 24, 'customSpace' => true, 'spaceTop' => 40, 'spaceBottom' => 9999 ) );
check( str_contains( $sh, '--fd-sh-title:96px' ) && str_contains( $sh, '--fd-sh-desc:24px' ) && str_contains( $sh, '--fd-sh-top:40px' ) && str_contains( $sh, '--fd-sh-bottom:240px' ) && str_contains( $sh, 'fd-section-heading--space' ), 'encabezado: tamaños y espacios ajustables, con límites' );
check( ! str_contains( fd_render_block( 'section-heading', array( 'title' => 'Hola' ) ), '--fd-sh-' ), 'encabezado: sin ajustes usa el diseño automático' );
$bs = fd_render_block( 'banner-carousel', array( 'slides' => array( array( 'imageId' => 1 ) ), 'customSpace' => true, 'spaceTop' => 12, 'spaceBottom' => 80 ) );
check( str_contains( $bs, '--fd-b-top:12px' ) && str_contains( $bs, '--fd-b-bottom:80px' ) && str_contains( $bs, 'fd-bancar--space' ), 'banner: espacio de arriba y abajo ajustable' );
foreach ( glob( dirname( __DIR__, 2 ) . '/src/blocks/*/block.json' ) as $bj ) {
	$j = json_decode( (string) file_get_contents( $bj ), true );
	if ( empty( $j['supports']['color']['background'] ) ) { check( false, 'fondo editable en ' . basename( dirname( $bj ) ) ); }
}
check( ! str_contains( (string) file_get_contents( dirname( __DIR__, 2 ) . '/src/css/banners.css' ), 'repeating-linear-gradient' ), 'banner: sin rayas en las fotos de atrás' );

$mq = fd_render_block( 'marquee', array( 'height' => 9, 'textSize' => 500 ) );
check( str_contains( $mq, '--fd-marquee-py:9px' ) && str_contains( $mq, '--fd-marquee-fs:40px' ), 'franja: alto y tamaño de letra ajustables, con límites' );
check( ! str_contains( (string) file_get_contents( dirname( __DIR__, 2 ) . '/src/css/marquee.css' ), 'hover' ), 'franja: no se detiene al pasar el mouse' );
echo $fail ? "\n$fail verificación(es) fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
