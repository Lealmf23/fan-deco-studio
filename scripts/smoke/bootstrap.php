<?php
/**
 * Stubs MÍNIMOS de WordPress/WooCommerce para humo-test del markup PHP del tema.
 * NO reemplaza probar en WordPress real: no valida hooks, REST, ni integración con WooCommerce.
 * Los datos de producto de acá son ficticios y solo existen en este arnés (no se empaquetan en el tema).
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_VERSION', 'test' );
define( 'FANDECO_DIR', dirname( __DIR__, 2 ) );
define( 'FANDECO_URI', '/theme' );

$GLOBALS['fd_mods']    = array();
$GLOBALS['fd_enqueue'] = array();

function __( $t ) { return $t; }
function esc_html__( $t ) { return htmlspecialchars( $t, ENT_QUOTES ); }
function esc_attr__( $t ) { return htmlspecialchars( $t, ENT_QUOTES ); }
function esc_html_e( $t ) { echo htmlspecialchars( $t, ENT_QUOTES ); }
function esc_attr_e( $t ) { echo htmlspecialchars( $t, ENT_QUOTES ); }
function _n( $s, $p, $n ) { return 1 === $n ? $s : $p; }
function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); }
function esc_attr( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); }
function esc_url( $u ) { $u = trim( (string) $u ); return preg_match( '#^(https?:|mailto:|/|\#)#i', $u ) ? htmlspecialchars( $u, ENT_QUOTES ) : ''; }
function esc_url_raw( $u ) { return esc_url( $u ); }
function sanitize_text_field( $t ) { return trim( strip_tags( (string) $t ) ); }
function sanitize_textarea_field( $t ) { return trim( strip_tags( (string) $t ) ); }
function wp_kses_post( $t ) { return preg_replace( '#<script.*?</script>#is', '', (string) $t ); }
function wp_strip_all_tags( $t ) { return strip_tags( (string) $t ); }
function absint( $n ) { return abs( (int) $n ); }
function wp_json_encode( $d ) { return json_encode( $d ); }
function antispambot( $e ) { return $e; }
function current_user_can() { return true; }
function get_theme_mod( $k, $d = false ) { return $GLOBALS['fd_mods'][ $k ] ?? $d; }
function get_bloginfo( $k = '' ) { return 'Fan Deco'; }
function bloginfo( $k = '' ) { echo 'UTF-8'; }
function home_url( $p = '' ) { return '/' . ltrim( $p, '/' ); }
function language_attributes() { echo 'lang="es-AR"'; }
function body_class( $c = '' ) { echo 'class="' . esc_attr( $c ) . '"'; }
function wp_body_open() {}
function wp_head() { echo "<script>document.documentElement.classList.add('js');if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.classList.add('fd-anim');}</script>"; echo '<link rel="stylesheet" href="' . esc_attr( $GLOBALS['fd_css'] ?? '' ) . '">'; }
function wp_footer() { echo '<script type="module" src="' . esc_attr( $GLOBALS['fd_js'] ?? '' ) . '"></script>'; }
function add_theme_support() {}
function add_action() {}
function add_filter( $tag, $cb ) { $GLOBALS['fd_filters'][ $tag ][] = $cb; }
function apply_filters( $tag, $value ) { foreach ( $GLOBALS['fd_filters'][ $tag ] ?? array() as $cb ) { $value = $cb( ...array_slice( func_get_args(), 1 ) ); } return $value; }
function remove_action() {}
function get_option( $k, $d = false ) { return array( 'woocommerce_enable_ajax_add_to_cart' => 'yes', 'default_product_cat' => 15 )[ $k ] ?? $d; }
function wp_attachment_is_image( $id ) { return $id > 0 && $id < 100; }
function wp_get_attachment_image( $id, $size = '', $icon = false, $attr = array() ) {
	if ( ! wp_attachment_is_image( $id ) ) { return ''; }
	$cls = $attr['class'] ?? '';
	$alt = $attr['alt'] ?? '';
	return sprintf( '<img src="%s" class="%s" alt="%s" width="800" height="1000">', esc_attr( $GLOBALS['fd_img'][ $id ] ?? '' ), esc_attr( $cls ), esc_attr( $alt ) );
}
function wp_get_attachment_image_url( $id ) { return $GLOBALS['fd_img'][ $id ] ?? ''; }
function wp_get_attachment_image_srcset() { return ''; }
function get_block_wrapper_attributes( $extra = array() ) {
	$parts = array( 'class="' . esc_attr( ( $extra['class'] ?? '' ) ) . '"' );
	foreach ( $extra as $k => $v ) { if ( 'class' !== $k ) { $parts[] = $k . '="' . esc_attr( $v ) . '"'; } }
	return implode( ' ', $parts );
}
function get_template_part( $slug, $name = null, $args = array() ) { include FANDECO_DIR . '/' . $slug . '.php'; }
function wp_nav_menu( $a ) {
	if ( ! empty( $a['fallback_cb'] ) && is_callable( $a['fallback_cb'] ) ) { ( $a['fallback_cb'] )(); return; }
	echo '<ul class="' . esc_attr( $a['menu_class'] ?? '' ) . '"><li class="menu-item"><a href="/">Inicio</a></li></ul>';
}
function wp_page_menu() {}
function get_search_query() { return ''; }
function get_the_archive_title() { return ''; }
function gmdate_stub() {}

// ---- WooCommerce fake ----
class WooCommerce {}
class WC_Product {
	public function __construct( public int $id, public string $name, public string $price, public bool $sale = false, public bool $featured = false, public bool $stock = true, public array $opts = array() ) {}
	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	public function get_permalink() { return '/producto/' . $this->id; }
	public function get_image_id() { return $this->id <= 8 ? $this->id : 0; }
	public function get_price_html() { return $this->sale ? '<del>$20.000</del> <ins>' . $this->price . '</ins>' : $this->price; }
	public function is_on_sale() { return $this->sale; }
	public function is_featured() { return $this->featured; }
	public function is_in_stock() { return $this->stock; }
	public function is_purchasable() { return true; }
	public function supports() { return empty( $this->opts ); }
	public function add_to_cart_url() { return $this->opts ? $this->get_permalink() : '/?add-to-cart=' . $this->id; }
	public function add_to_cart_text() { return $this->opts ? 'Elegir opciones' : 'Agregar al carrito'; }
	public function add_to_cart_description() { return $this->add_to_cart_text(); }
	public function get_sku() { return ''; }
}
class WC_Product_Variable extends WC_Product {
	public function get_variation_attributes() { return array( 'pa_color' => $this->opts ); }
	public function get_id_stub() {}
}
function fd_products() {
	return array(
		new WC_Product( 1, 'Producto de prueba A', '$15.000', true, true ),
		new WC_Product( 2, 'Producto de prueba B', '$12.000', false, true ),
		new WC_Product_Variable( 3, 'Producto de prueba C (variable)', '$18.000', false, false, true, array( 'negro', 'blanco', 'rojo', 'verde', 'azul', 'rosa' ) ),
		new WC_Product( 4, 'Producto de prueba D', '$9.000', false, false, false ),
	);
}
function fandeco_has_woocommerce_stub() {}
function wc_get_products( $a ) {
	$all = fd_products();
	if ( ! empty( $a['include'] ) ) { $all = array_values( array_filter( $all, fn( $p ) => in_array( $p->id, $a['include'], true ) ) ); }
	return array_slice( $all, 0, $a['limit'] ?? 8 );
}
function wc_get_product_ids_on_sale() { return array( 1 ); }
function wc_placeholder_img() { return '<img src="" alt="">'; }
function wc_get_page_permalink( $p ) { return '/' . $p . '/'; }
function wc_get_cart_url() { return '/carrito/'; }
function get_terms( $a ) {
	$t = array();
	foreach ( array( 'Lámparas', 'Mascotas', 'Regalos', 'Decoración' ) as $i => $n ) {
		$t[] = (object) array( 'term_id' => 20 + $i, 'name' => $n, 'slug' => sanitize_title_stub( $n ), 'description' => 'Descripción de ' . $n, 'count' => 3 + $i );
	}
	if ( ! empty( $a['include'] ) ) { $t = array_values( array_filter( $t, fn( $x ) => in_array( $x->term_id, $a['include'], true ) ) ); }
	return $t;
}
function sanitize_title_stub( $n ) { return strtolower( preg_replace( '/[^a-z]/i', '', iconv( 'UTF-8', 'ASCII//TRANSLIT', $n ) ) ); }
function get_term_meta( $id, $key = '', $single = true ) {
	if ( 'fandeco_swatch_color' === $key ) { return 101 === $id ? '#111111' : ''; }
	if ( 'blocksy_taxonomy_meta_options' === $key ) { return 103 === $id ? array( 'x' => array( 'accent_color' => array( 'default' => array( 'color' => '#AA0000' ) ) ) ) : ''; }
	return $id - 19 <= 4 && $id >= 20 ? ( $id - 19 ) : 0;
}
function get_term_link( $t ) { return '/categoria/' . $t->slug; }
function taxonomy_exists( $t ) { return true; }
function get_term_by( $f, $v, $t ) { static $ids = array( 'negro' => 101, 'blanco' => 102, 'rojo' => 103, 'lila-raro' => 104, 'chico' => 105, 'grande' => 106 ); return isset( $ids[ $v ] ) ? (object) array( 'term_id' => $ids[ $v ], 'name' => ( 'lila-raro' === $v ? 'Lila <b>raro</b>' : ucfirst( $v ) ), 'slug' => $v ) : false; }
function remove_accents( $s ) { return iconv( 'UTF-8', 'ASCII//TRANSLIT', $s ); }
function sanitize_title( $s ) { return trim( strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $s ) ), '-' ); }
function wc_attribute_taxonomy_id_by_name( $n ) { return array( 'pa_color' => 1, 'pa_base' => 2, 'pa_tamano' => 3, 'pa_otro' => 4 )[ $n ] ?? 0; }
function wc_get_attribute( $id ) { return (object) array( 'type' => array( 1 => 'color', 2 => 'color', 3 => 'button', 4 => 'select' )[ $id ] ); }
function wc_attribute_label( $n ) { return array( 'pa_color' => 'Color', 'pa_tamano' => 'Tamaño' )[ $n ] ?? $n; }
function sanitize_hex_color( $c ) { return preg_match( '/^#[0-9a-f]{6}$/i', (string) $c ) ? $c : ''; }
function woocommerce_template_loop_add_to_cart() { global $product; $v = $product instanceof WC_Product_Variable; echo '<a href="' . esc_url( $product->add_to_cart_url() ) . '" class="button ' . ( $v ? 'product_type_variable' : 'product_type_simple add_to_cart_button ajax_add_to_cart' ) . '" data-product_id="' . $product->get_id() . '" rel="nofollow">' . esc_html( $product->add_to_cart_text() ) . '</a>'; }
function is_front_page() { return $GLOBALS['t_front'] ?? false; }
function is_shop() { return false; }
function is_product_taxonomy() { return false; }
function is_cart() { return false; }
function is_account_page() { return false; }
function is_wp_error( $x ) { return false; }
function get_term( $id ) { return (object) array( 'slug' => 'lamparas', 'name' => 'Lámparas' ); }

// Tema (carga real, no stub).
require FANDECO_DIR . '/inc/helpers.php';
require FANDECO_DIR . '/inc/menus.php';
require FANDECO_DIR . '/inc/woocommerce.php';
require FANDECO_DIR . '/inc/swatches.php';

/** Render directo de un bloque con sus atributos por defecto de block.json + overrides. */
function fd_render_block( string $name, array $override = array() ): string {
	$dir  = FANDECO_DIR . '/build/blocks/' . $name;
	$meta = json_decode( file_get_contents( $dir . '/block.json' ), true );
	$attributes = array();
	foreach ( $meta['attributes'] as $k => $def ) { $attributes[ $k ] = $def['default'] ?? null; }
	$attributes = array_merge( $attributes, $override );
	ob_start();
	( static function ( $attributes ) use ( $dir ) { include $dir . '/render.php'; } )( $attributes );
	return (string) ob_get_clean();
}
