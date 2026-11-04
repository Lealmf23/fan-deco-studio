<?php
/**
 * Uso: php scripts/smoke/run.php [--html=ruta.html --css=... --js=...]
 * Renderiza todos los bloques y plantillas con stubs y verifica invariantes básicos.
 */
require __DIR__ . '/bootstrap.php';

$opts = getopt( '', array( 'html::', 'css::', 'js::', 'images::', 'lit::' ) );
$GLOBALS['fd_css'] = $opts['css'] ?? '';
$GLOBALS['fd_js']  = $opts['js'] ?? '';
$GLOBALS['fd_img'] = array();
if ( ! empty( $opts['images'] ) ) {
	foreach ( glob( $opts['images'] . '/*' ) as $i => $f ) { $GLOBALS['fd_img'][ preg_match( '/^(\d+)/', basename( $f ), $m ) ? (int) $m[1] : $i + 1 ] = $f; }
}

$fail = 0;
function check( bool $cond, string $msg ): void {
	global $fail;
	echo ( $cond ? "  ok   " : "  FAIL " ) . $msg . "\n";
	if ( ! $cond ) { $fail++; }
}

echo "Bloques\n";
$hero = fd_render_block( 'hero' );
check( str_contains( $hero, '<h1' ) && str_contains( $hero, 'Diseñamos lo que imaginás' ), 'hero: h1 con título por defecto' );
check( str_contains( $hero, 'href="/shop/"' ), 'hero: botón cae a la URL de la tienda si no se define' );
check( str_contains( $hero, 'fd-media-placeholder' ), 'hero: placeholder sin imagen (no inventa fotos)' );
$xss = fd_render_block( 'hero', array( 'title' => '<script>alert(1)</script>Hola', 'buttonUrl' => 'javascript:alert(1)', 'variant' => 'evil"><x', 'desktopImageId' => 0 ) );
check( ! str_contains( $xss, '<script>' ), 'hero: título saneado/escapado' );
check( ! str_contains( $xss, 'javascript:' ), 'hero: URL javascript: descartada' );
check( ! str_contains( $xss, 'evil' ), 'hero: variante fuera de la lista cae al valor por defecto' );

$bc = fd_render_block( 'banner-carousel', array( 'slides' => array(
	array( 'imageId' => 1, 'alt' => 'Lámpara naranja', 'url' => '/categoria/lamparas' ),
	array( 'imageId' => 2, 'alt' => 'Calendario' ),
	array( 'imageId' => 0, 'alt' => 'sin imagen' ),
	array( 'imageId' => 999, 'alt' => 'imagen inexistente' ),
	array( 'imageId' => 3, 'alt' => '<script>x</script>', 'url' => 'javascript:alert(1)' ),
) ) );
check( 3 === substr_count( $bc, 'fd-bancar__slide' ) - substr_count( $bc, 'fd-bancar__slide-' ), 'banner-carousel: descarta banners sin imagen válida (3 de 5)' );
check( str_contains( $bc, 'aria-roledescription="carrusel"' ) && str_contains( $bc, 'aria-label="1 de 3"' ) && str_contains( $bc, 'data-fd-bancar-prev' ) && str_contains( $bc, 'data-fd-bancar-pause' ), 'banner-carousel: pila accesible, con flechas y pausa' );
check( ! str_contains( $bc, '<script>' ) && ! str_contains( $bc, 'javascript:' ), 'banner-carousel: alt saneado y URL javascript: descartada' );
check( str_contains( $bc, '<a class="fd-bancar__link" href="/categoria/lamparas">' ) && 1 === substr_count( $bc, 'fd-bancar__link"' ), 'banner-carousel: enlace solo donde se definió (el de javascript: se descarta)' );
$bct = fd_render_block( 'banner-carousel', array( 'eyebrow' => 'Diseño', 'title' => '**Hacemos** *tus ideas*', 'description' => 'Texto <b>x</b>', 'buttonText' => 'Ver', 'buttonUrl' => '/shop/', 'level' => 1, 'slides' => array( array( 'imageId' => 1, 'alt' => 'Uno' ), array( 'imageId' => 2, 'alt' => 'Dos' ) ) ) );
check( str_contains( $bct, 'fd-bancar--split' ) && str_contains( $bct, '<h1 class="fd-bancar__title"' ) && str_contains( $bct, '<em>tus ideas</em>' ) && str_contains( $bct, 'href="/shop/"' ) && ! str_contains( $bct, '<b>' ), 'banner-carousel: texto a la izquierda (modo dividido), H1, cursiva y botón; sin HTML ajeno' );
check( ! str_contains( $bc, 'fd-bancar--split' ) && ! str_contains( $bc, 'fd-bancar__text' ), 'banner-carousel: sin texto no hay columna de texto' );
check( str_contains( fd_render_block( 'banner-carousel', array( 'title' => 'x', 'level' => 9, 'slides' => array( array( 'imageId' => 1 ) ) ) ), '<h1 ' ), 'banner-carousel: nivel de título inválido cae a H1' );
$bc1 = fd_render_block( 'banner-carousel', array( 'slides' => array( array( 'imageId' => 1, 'alt' => 'Uno' ) ), 'autoplay' => true ) );
check( ! str_contains( $bc1, 'data-fd-bancar-prev' ) && str_contains( $bc1, 'data-autoplay="0"' ), 'banner-carousel: con un solo banner no hay controles ni avance' );
check( ! str_contains( fd_render_block( 'banner-carousel', array( 'slides' => array() ) ), '<section' ), 'banner-carousel: sin banners no imprime el carrusel (solo un aviso para quien edita)' );
check( str_contains( fd_render_block( 'section-heading', array( 'title' => 'Hola', 'level' => 1 ) ), '<h1 ' ), 'section-heading: admite H1 (título principal de la página)' );
check( str_contains( fd_render_block( 'section-heading', array( 'title' => 'Hola', 'level' => 7 ) ), '<h2 ' ), 'section-heading: nivel inválido cae a H2' );

$mq = fd_render_block( 'marquee', array( 'items' => "DISEÑO\n<b>COLOR</b>\n\nHECHO PARA VOS", 'speed' => 999 ) );
check( 6 === substr_count( $mq, '<span>' ) && 6 === substr_count( $mq, '<span class="is-dup">' ) && ! str_contains( $mq, '<b>' ) && str_contains( $mq, '--fd-marquee-speed:90s' ), 'marquee: ítems saneados, sin líneas vacías y velocidad acotada' );
check( str_contains( $mq, 'aria-hidden="true"' ) && str_contains( $mq, 'aria-label="DISEÑO, COLOR, HECHO PARA VOS"' ), 'marquee: contenido repetido oculto a lectores de pantalla y nombre accesible' );
check( '' === trim( fd_render_block( 'marquee', array( 'items' => " \n " ) ) ), 'marquee: sin ítems no imprime nada' );
check( '<strong>Hacemos</strong> <em>tus ideas</em>' === fandeco_title_html( '**Hacemos** *tus ideas*' ) && ! str_contains( fandeco_title_html( '<script>x</script> *a*' ), '<script>' ), 'títulos: **negrita** y *cursiva* con el resto escapado' );
check( str_contains( fd_render_block( 'section-heading', array( 'title' => 'Elegí tu próxima *favorita.*' ) ), '<em>favorita.</em>' ), 'section-heading: título con cursiva' );

$hero_lit = fd_render_block( 'hero', array( 'variant' => 'immersive', 'desktopImageId' => 21, 'imageLitId' => 22 ) );
check( str_contains( $hero_lit, 'data-fd-hero-switch' ) && str_contains( $hero_lit, 'role="switch"' ) && str_contains( $hero_lit, 'fd-hero__lit' ), 'hero: con foto encendida hay interruptor accesible y capa de luz' );
check( ! str_contains( $hero_lit, 'class="fd-hero fd-hero--immersive" ' ) && ! str_contains( $hero_lit, ' is-lit' ), 'hero: arranca apagado por defecto' );
check( str_contains( fd_render_block( 'hero', array( 'variant' => 'immersive', 'desktopImageId' => 21, 'imageLitId' => 22, 'startLit' => true ) ), ' is-lit' ), 'hero: puede arrancar encendido' );
$hero_plain = fd_render_block( 'hero', array( 'variant' => 'immersive', 'desktopImageId' => 21 ) );
check( ! str_contains( $hero_plain, 'data-fd-hero-switch' ) && ! str_contains( $hero_plain, 'fd-hero__lit' ), 'hero: sin foto encendida no hay interruptor (no se simula la luz)' );
$cats = fd_render_block( 'category-grid', array( 'showDescription' => true, 'showCount' => true ) );
check( substr_count( $cats, 'fd-cat-grid__item' ) === 4, 'category-grid: 4 categorías' );
check( str_contains( $cats, 'Mascotas' ) && str_contains( $cats, 'href="/categoria/mascotas"' ), 'category-grid: enlaces a categorías' );

$prods = fd_render_block( 'featured-products' );
check( substr_count( $prods, 'fd-product-card"' ) === 4 || substr_count( $prods, 'class="fd-product-card"' ) === 4, 'featured-products: 4 tarjetas' );
check( str_contains( $prods, 'data-fd-carousel' ), 'featured-products: carrusel por defecto' );
check( str_contains( $prods, 'ajax_add_to_cart' ), 'featured-products: AJAX add to cart en simples' );
check( str_contains( $prods, 'Elegir opciones' ), 'featured-products: variable enlaza a elegir opciones' );
check( str_contains( $prods, 'fd-product-card__options' ) && str_contains( $prods, 'class="fd-dot"' ), 'featured-products: puntos de color dentro de la imagen' );
check( ! str_contains( $prods, '<li>negro</li>' ), 'featured-products: ya no hay chips de texto bajo el precio' );
check( str_contains( $prods, 'fd-product-card__window' ) && str_contains( $prods, 'fd-product-card__add' ), 'featured-products: ventana de producto y botón de compra' );
check( str_contains( $prods, 'Sin stock' ) && str_contains( $prods, 'Oferta' ), 'featured-products: etiquetas reales (oferta/sin stock)' );
$manual = fd_render_block( 'featured-products', array( 'source' => 'manual', 'productIds' => array( 2, 1 ), 'layout' => 'grid' ) );
check( strpos( $manual, 'prueba B' ) < strpos( $manual, 'prueba A' ), 'featured-products: respeta el orden manual' );
check( ! str_contains( $manual, 'data-fd-carousel' ), 'featured-products: layout grilla' );

$banner = fd_render_block( 'editorial-banner', array( 'title' => 'Tu mascota', 'mount' => 'configurator', 'badge' => 'Próximamente' ) );
check( str_contains( $banner, 'data-fd-mount="configurator"' ) && str_contains( $banner, 'Próximamente' ), 'editorial-banner: punto de montaje + etiqueta honesta' );
check( str_contains( fd_render_block( 'category-grid', array( 'layout' => 'mosaic' ) ), 'fd-cats--mosaic fd-cats--n4' ), 'category-grid: mosaico con cantidad de ítems' );
check( str_contains( fd_render_block( 'benefits', array( 'variant' => 'strip' ) ), 'fd-benefits--strip' ), 'benefits: franja' );
check( str_contains( fd_render_block( 'process-steps', array( 'variant' => 'index', 'title' => 'T' ) ), 'fd-process__side' ), 'process-steps: índice con título lateral' );
check( str_contains( fd_render_block( 'featured-products', array( 'layout' => 'editorial', 'title' => 'Destacados' ) ), 'fd-featured__side' ), 'featured-products: editorial con título lateral' );
check( str_contains( fd_render_block( 'editorial-banner', array( 'variant' => 'immersive', 'imageId' => 23, 'title' => 'x' ) ), 'fd-banner--immersive' ), 'editorial-banner: inmersivo' );
$benefits = fd_render_block( 'benefits' );
check( str_contains( $benefits, '10% OFF' ) && substr_count( $benefits, 'fd-benefit"' ) === 3, 'benefits: ítems por defecto' );
$steps = fd_render_block( 'process-steps' );
check( substr_count( $steps, 'class="fd-step"' ) === 4 && str_contains( $steps, '01' ), 'process-steps: 4 etapas numeradas' );
$bad = fd_render_block( 'benefits', array( 'items' => array( array( 'icon' => 'no-existe', 'title' => 'X', 'text' => '' ), 'string-suelto' ) ) );
check( substr_count( $bad, 'fd-benefit"' ) === 1, 'benefits: ítems inválidos ignorados, icono desconocido tolerado' );
$head = fd_render_block( 'section-heading', array( 'title' => 'Hola', 'level' => 3, 'linkText' => 'Ver', 'linkUrl' => '/x' ) );
check( str_contains( $head, '<h3' ), 'section-heading: nivel configurable' );

echo "Selectores de variaciones\n";
check( fandeco_find_hex( array( 'a' => array( 'b' => '#AbC' ) ) ) === '#AbC', 'find_hex: busca anidado' );
check( fandeco_find_hex( 'var(--x)' ) === '' && fandeco_find_hex( 'red' ) === '', 'find_hex: ignora valores no hexadecimales' );
$sel = '<select id="pa_color" name="attribute_pa_color"><option value="">Elegir</option><option value="negro">Negro</option></select>';
$f = $GLOBALS['fd_filters']['woocommerce_dropdown_variation_attribute_options_html'][0];
$out = $f( $sel, array( 'attribute' => 'pa_color', 'options' => array( 'negro', 'blanco', 'rojo', 'lila-raro' ), 'selected' => 'blanco', 'product' => null ) );
check( str_contains( $out, '<select id="pa_color"' ) && str_contains( $out, 'fd-swatch-source" hidden' ), 'swatches: conserva el <select> original (oculto)' );
check( str_contains( $out, '--fd-swatch:#111111' ), 'swatches: color desde el campo propio' );
check( str_contains( $out, '--fd-swatch:#ffffff' ), 'swatches: color deducido del nombre (blanco)' );
check( str_contains( $out, '--fd-swatch:#AA0000' ), 'swatches: color heredado de Blocksy (búsqueda tolerante)' );
check( preg_match( '/data-value="blanco"[^>]*>/', $out ) && str_contains( $out, 'aria-checked="true" data-value="blanco"' ), 'swatches: opción seleccionada marcada' );
check( str_contains( $out, 'fd-swatch--button' ) && str_contains( $out, 'Lila &lt;b&gt;raro&lt;/b&gt;' ), 'swatches: color desconocido cae a botón con texto escapado' );
check( str_contains( $out, 'role="radiogroup"' ) && str_contains( $out, 'aria-label="Color"' ), 'swatches: radiogroup accesible con etiqueta' );
$sz = $f( '<select></select>', array( 'attribute' => 'pa_tamano', 'options' => array( 'chico', 'grande' ), 'selected' => '', 'product' => null ) );
check( substr_count( $sz, 'fd-swatch--button' ) === 2 && ! str_contains( $sz, '--fd-swatch' ), 'swatches: atributo tipo botón (tamaño)' );
check( $f( $sel, array( 'attribute' => 'pa_otro', 'options' => array( 'x' ) ) ) === $sel, 'swatches: atributos tipo select no se tocan' );
check( $f( $sel, array( 'attribute' => 'color-personalizado', 'options' => array( 'x' ) ) ) === $sel, 'swatches: atributos no globales no se tocan' );

echo "Indicador de pasos\n";
foreach ( array( 1, 2, 3 ) as $n ) {
	ob_start(); fandeco_checkout_steps( $n ); $st = ob_get_clean();
	check( substr_count( $st, 'fd-stepper__item' ) === 3 && substr_count( $st, 'aria-current="step"' ) === 1 && substr_count( $st, 'is-done' ) === $n - 1, "pasos: paso $n actual, " . ( $n - 1 ) . ' completados, accesible' );
}

echo "Mi cuenta (ganchos)\n";
check( str_contains( file_get_contents( FANDECO_DIR . '/inc/woocommerce.php' ), "'woocommerce_before_customer_login_form'" ) && str_contains( file_get_contents( FANDECO_DIR . '/inc/woocommerce.php' ), "'woocommerce_account_dashboard'" ), 'cuenta: panel de bienvenida y accesos rápidos por ganchos oficiales (sin sobrescribir plantillas)' );
check( ! is_dir( FANDECO_DIR . '/woocommerce' ), 'tema: no sobrescribe plantillas de WooCommerce (carpeta woocommerce/ ausente)' );

check( str_contains( file_get_contents( FANDECO_DIR . '/page.php' ), "<!-- wp:fandeco/" ) && str_contains( file_get_contents( FANDECO_DIR . '/docs/portada-prueba.html' ), 'wp:fandeco/hero' ), 'page.php: páginas con bloques Fan Deco a ancho completo y contenido de prueba disponible' );

echo "Vista rápida, panel del carrito y compartir\n";
if ( ! function_exists( 'is_cart' ) ) { function is_cart() { return false; } }
if ( ! function_exists( 'is_checkout' ) ) { function is_checkout() { return false; } }
if ( ! function_exists( 'wc_get_checkout_url' ) ) { function wc_get_checkout_url() { return '/finalizar-compra/'; } }
ob_start(); fandeco_print_shop_dialogs(); $dlg = ob_get_clean();
check( str_contains( $dlg, 'data-fd-qv' ) && str_contains( $dlg, 'data-fd-cartpanel' ) && str_contains( $dlg, '/finalizar-compra/' ) && 2 === substr_count( $dlg, '<dialog' ), 'diálogos: vista rápida y panel del carrito con enlaces al checkout' );
check( 3 <= substr_count( $dlg, 'aria-label=' ), 'diálogos: con nombre accesible y botones de cierre' );
$sh = fandeco_share_links( 'https://fandeco.com.ar/p/lampara?a=1&b=2', 'Lámpara "Cúpula" & luz' );
check( str_starts_with( $sh['whatsapp'][1], 'https://wa.me/?text=' ) && ! str_contains( $sh['whatsapp'][1], ' ' ) && str_contains( $sh['facebook'][1], rawurlencode( 'https://fandeco.com.ar/p/lampara?a=1&b=2' ) ), 'compartir: enlaces codificados (sin espacios ni caracteres crudos)' );
check( str_starts_with( $sh['email'][1], 'mailto:?subject=' ) && ! str_contains( $sh['email'][1], '"' ), 'compartir: email con asunto codificado' );
check( str_contains( file_get_contents( FANDECO_DIR . '/template-parts/components/product-card.php' ), 'data-fd-quickview' ), 'tarjeta: botón de vista rápida con nombre accesible' );

$pg = file_get_contents( FANDECO_DIR . '/page.php' );
check( str_contains( $pg, 'Elementor' ) && str_contains( $pg, "Plugin' )" ) && str_contains( $pg, '$fd_elementor_on &&' ) && str_contains( $pg, "stripos( (string) get_post_field( 'post_content'" ), 'page.php: páginas de Elementor se ven bien si Elementor se desactiva y no duplican el H1' );

echo "Plantillas\n";
foreach ( array( 'header.php', 'footer.php' ) as $tpl ) {
	ob_start();
	include FANDECO_DIR . '/' . $tpl;
	$out = ob_get_clean();
	check( strlen( $out ) > 500, "$tpl renderiza sin errores" );
	if ( 'header.php' === $tpl ) {
		check( str_contains( $out, 'fd-cart-count' ) && str_contains( $out, 'aria-controls="fd-drawer"' ), 'header: carrito con contador y botón de menú accesible' );
		check( str_contains( $out, 'fd-logo__fan' ), 'header: logo tipográfico de reserva (fan / DECO)' );
		check( str_contains( $out, 'fd-mobile-search' ) && str_contains( $out, 'name="post_type" value="product"' ), 'header: buscador móvil de productos' );
		check( str_contains( $out, 'fd-drawer-search' ) && str_contains( $out, 'fd-menu fd-menu--mobile' ) && str_contains( $out, 'fd-drawer__extras' ), 'header: menú a pantalla completa con buscador, menú móvil y accesos' );
		check( str_contains( $out, 'fd-search-hint' ) && str_contains( $out, 'fd-chips' ), 'header: panel de búsqueda con atajos a categorías' );
		check( str_contains( $out, 'fd-search-label' ), 'header: buscador con etiqueta visible en escritorio' );
		$header_out = $out;
	} else {
		check( str_contains( $out, 'class="fd-tabbar"' ) && substr_count( $out, 'class="fd-tab' ) >= 4, 'footer: barra inferior móvil con 4 accesos' );
		check( str_contains( $out, 'fd-tab-cart' ), 'footer: carrito con fragmento propio (no pisa el del header)' );
		check( ! str_contains( $out, 'fd-footer__cta' ), 'footer: sin WhatsApp ni email no muestra la franja de invitación' );
		$footer_out = $out;
	}
}

if ( ! empty( $opts['html'] ) ) {
	$home  = fd_render_block( 'banner-carousel', array( 'eyebrow' => 'DISEÑO + IMPRESIÓN 3D + PERSONALIZACIÓN', 'title' => '**Hacemos** *tus ideas* **realidad.**', 'description' => 'Objetos personalizados, deco y regalos únicos diseñados y fabricados especialmente para vos.', 'buttonText' => 'Ver la tienda', 'buttonUrl' => '/shop/', 'slides' => array( array( 'imageId' => 1, 'alt' => 'Lámpara naranja' ), array( 'imageId' => 2, 'alt' => 'Calendario' ), array( 'imageId' => 3, 'alt' => 'Veladores' ), array( 'imageId' => 4, 'alt' => 'Mates de fútbol' ) ) ) );
	$home .= fd_render_block( 'marquee', array() );
	$home .= fd_render_block( 'benefits', array( 'variant' => 'strip' ) );
	$home .= fd_render_block( 'section-heading', array( 'eyebrow' => 'PARA CADA ESTILO', 'title' => 'Elegí tu próxima *favorita.*', 'description' => 'Pequeños objetos que hacen grande la diferencia.', 'align' => 'center' ) );
	$home .= fd_render_block( 'category-grid', array( 'layout' => 'mosaic' ) );
	$home .= fd_render_block( 'featured-products', array( 'layout' => 'editorial', 'title' => 'Productos destacados', 'description' => 'Piezas impresas en 3D, en los colores y estilos que elijas.', 'linkText' => 'Ver toda la tienda', 'linkUrl' => '/shop/' ) );
	$home .= fd_render_block( 'editorial-banner', array( 'eyebrow' => 'Personalizados', 'title' => 'Tu mascota, para siempre', 'description' => 'Convertimos a tu compañero en una pieza única, impresa en 3D y llena de detalles.', 'buttonText' => 'Ver mascotas', 'buttonUrl' => '/mascotas', 'variant' => 'immersive', 'imageId' => 23 ) );
	$home .= fd_render_block( 'editorial-banner', array( 'eyebrow' => 'Personalizador 3D', 'badge' => 'Próximamente', 'title' => 'Diseñá tu pieza en 3D', 'description' => 'Estamos preparando un configurador para ver tu producto en 360°, cambiar colores y acabados.', 'variant' => 'dark', 'mount' => 'configurator' ) );
	$home .= fd_render_block( 'process-steps', array( 'variant' => 'index', 'title' => 'Del diseño a tu casa', 'description' => 'Cuatro etapas, de la idea al envío.' ) );
	$GLOBALS['fd_mods'] = array( 'fandeco_announcement' => '10% OFF con transferencia bancaria', 'fandeco_email' => 'hola@example.com', 'fandeco_social_instagram' => 'https://instagram.com/x', 'fandeco_payment_methods' => "Mercado Pago\nTransferencia", 'fandeco_shipping_info' => 'Texto de envíos de ejemplo', 'fandeco_footer_about' => 'Diseñamos y fabricamos objetos decorativos con impresión 3D.' );
	ob_start(); include FANDECO_DIR . '/header.php'; $h = ob_get_clean();
	ob_start(); echo '<main id="main" class="fd-main fd-home" tabindex="-1">' . $home . '</main>'; include FANDECO_DIR . '/footer.php'; $f = ob_get_clean();
	check( str_contains( $f, 'fd-footer__cta' ) && str_contains( $f, 'mailto:hola@example.com' ) && ! str_contains( $f, 'Escribinos por WhatsApp</a>' ), 'footer: franja con email y sin botón de WhatsApp cuando no hay número' );
	file_put_contents( $opts['html'], $h . $f );
	echo "HTML: {$opts['html']}\n";
}

// Medios de contacto: solo los configurados, enlaces seguros.
$GLOBALS['fd_mods'] = array( 'fandeco_whatsapp' => '+54 9 11 5555-1234', 'fandeco_email' => 'hola@fandeco.com.ar', 'fandeco_phone' => '+54 11 5555-1234', 'fandeco_address' => '', 'fandeco_hours' => '' );
$cm = fandeco_contact_methods();
check( 4 === count( $cm ) + 1 && 3 === count( $cm ), 'contacto: solo aparecen los medios con dato (3 de 5)' );
check( 'https://wa.me/5491155551234?text=' === substr( $cm[0]['url'], 0, 33 ), 'contacto: WhatsApp solo con dígitos' );
check( 'tel:+541155551234' === $cm[2]['url'] && 'mailto:hola@fandeco.com.ar' === $cm[1]['url'], 'contacto: enlaces tel: y mailto: limpios' );
$GLOBALS['fd_mods'] = array();
check( array() === fandeco_contact_methods(), 'contacto: sin datos no muestra nada' );

echo $fail ? "\n$fail verificación(es) fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
