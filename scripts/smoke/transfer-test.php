<?php
/** Pruebas del descuento por transferencia: cálculo puro + gancho cart_fee con carrito/sesión simulados. */
define( 'FANDECO_CORE_TEST', true );
define( 'ABSPATH', __DIR__ . '/' );

// ---- Stubs mínimos de WordPress/WooCommerce ----
$GLOBALS['t_opts'] = array();
$GLOBALS['t_checkout'] = true;
$GLOBALS['t_chosen'] = 'bacs';
$GLOBALS['t_tax'] = true;
$GLOBALS['t_active_plugins'] = array();
function add_action() {} function add_filter() {}
function get_option( $k, $d = false ) { return 'fandeco_core_transfer' === $k ? $GLOBALS['t_opts'] : ( 'active_plugins' === $k ? $GLOBALS['t_active_plugins'] : $d ); }
function is_admin() { return false; }
function is_checkout() { return $GLOBALS['t_checkout']; }
function wc_get_price_decimals() { return 2; }
function wc_tax_enabled() { return $GLOBALS['t_tax']; }
function WC() { return new class { public $session; function __construct() { $this->session = new class { function get( $k ) { return $GLOBALS['t_chosen']; } }; } }; }
function wc_get_product_term_ids( $pid, $tax ) { return array( 10 => array( 20 ), 11 => array( 21 ), 12 => array( 20, 21 ) )[ $pid ] ?? array(); }
function get_ancestors( $id, $tax, $type ) { return 21 === $id ? array( 5 ) : array(); } // 21 es subcategoría de 5
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_key( $s ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $s ) ); }
function absint( $n ) { return abs( (int) $n ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
class WC_Product { function __construct( public int $id, public int $parent = 0 ) {} function get_id() { return $this->id; } function get_parent_id() { return $this->parent; } }
class WC_Cart {
	public array $items = array(); public array $fees = array();
	function get_cart() { return $this->items; }
	function add_fee( $name, $amount, $taxable, $class ) { $this->fees[] = compact( 'name', 'amount', 'taxable', 'class' ); }
}
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-transfer-calc.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-transfer-discount.php';

$fail = 0;
function t( string $n, $got, $want ): void {
	global $fail;
	$ok = $got === $want;
	echo ( $ok ? '  ok   ' : '  FAIL ' ) . $n . ( $ok ? '' : "\n         got: " . var_export( $got, true ) . "\n         want: " . var_export( $want, true ) ) . "\n";
	if ( ! $ok ) { ++$fail; }
}
function cart( array $lines ): WC_Cart { $c = new WC_Cart(); foreach ( $lines as $l ) { $c->items[] = array( 'data' => new WC_Product( $l[0], $l[1] ?? 0 ), 'line_total' => $l[2] ); } return $c; }
function run( array $opts, array $lines ) { $GLOBALS['t_opts'] = $opts; $c = cart( $lines ); FanDeco_Transfer_Discount::cart_fee( $c ); return $c->fees; }
// options() cachea con static: forzamos recarga vía reflexión no posible -> usamos proceso por escenario en sub-scripts? Más simple: reiniciar caché.
function reset_cache() { $r = new ReflectionMethod( 'FanDeco_Transfer_Discount', 'options' ); }

echo "Cálculo puro\n";
$lines = array(
	array( 'product_id' => 1, 'parent_id' => 10, 'categories' => array( 20 ), 'total' => 10000.0 ),
	array( 'product_id' => 2, 'parent_id' => 11, 'categories' => array( 21, 5 ), 'total' => 5000.0 ),
);
t( 'todos: 10 % de 15000', FanDeco_Transfer_Calc::amount( $lines, 10, 'all', array(), array() ), 1500.0 );
t( 'categoría 20: solo la primera línea', FanDeco_Transfer_Calc::amount( $lines, 10, 'categories', array( 20 ), array() ), 1000.0 );
t( 'categoría padre 5 incluye subcategoría', FanDeco_Transfer_Calc::amount( $lines, 10, 'categories', array( 5 ), array() ), 500.0 );
t( 'productos: por ID de producto', FanDeco_Transfer_Calc::amount( $lines, 20, 'products', array(), array( 2 ) ), 1000.0 );
t( 'productos: por ID del padre (variaciones)', FanDeco_Transfer_Calc::amount( $lines, 20, 'products', array(), array( 10 ) ), 2000.0 );
t( 'alcance sin coincidencias = 0', FanDeco_Transfer_Calc::amount( $lines, 10, 'categories', array( 99 ), array() ), 0.0 );
t( 'alcance desconocido = 0', FanDeco_Transfer_Calc::amount( $lines, 10, 'otro', array(), array() ), 0.0 );
t( '0 % = 0', FanDeco_Transfer_Calc::amount( $lines, 0, 'all', array(), array() ), 0.0 );
t( 'porcentaje se limita a 99,99', FanDeco_Transfer_Calc::clamp_percent( '150' ), 99.99 );
t( 'coma decimal', FanDeco_Transfer_Calc::clamp_percent( '7,5' ), 7.5 );
t( 'negativo -> 0', FanDeco_Transfer_Calc::clamp_percent( -5 ), 0.0 );
t( 'redondeo a 2 decimales', FanDeco_Transfer_Calc::amount( array( array( 'product_id' => 1, 'parent_id' => 1, 'categories' => array(), 'total' => 33.33 ) ), 10, 'all', array(), array() ), 3.33 );
t( 'nunca supera la base', FanDeco_Transfer_Calc::amount( $lines, 99.99, 'all', array(), array() ) <= 15000.0, true );

echo "Gancho cart_fee (carrito/sesión simulados)\n";
$base = array( 'enabled' => 1, 'percent' => 10, 'label' => 'Descuento por transferencia', 'gateways' => array( 'bacs' ), 'scope' => 'all', 'categories' => array(), 'products' => array(), 'taxable' => 1, 'badge' => 1, 'confirm_no_overlap' => 0 );
// Cada escenario en un proceso nuevo no es necesario: options() es estática, así que cambiamos de enfoque: sub-procesos.
if ( isset( $argv[1] ) ) {
	$sc = json_decode( base64_decode( $argv[1] ), true );
	$GLOBALS['t_checkout'] = $sc['checkout'] ?? true;
	$GLOBALS['t_chosen'] = $sc['chosen'] ?? 'bacs';
	$GLOBALS['t_tax'] = $sc['tax'] ?? true;
	$GLOBALS['t_active_plugins'] = $sc['plugins'] ?? array();
	echo json_encode( run( $sc['opts'], $sc['lines'] ) );
	exit;
}
function scenario( array $sc ): array {
	$out = shell_exec( 'php ' . escapeshellarg( __FILE__ ) . ' ' . escapeshellarg( base64_encode( json_encode( $sc ) ) ) );
	$line = trim( (string) strrchr( "\n" . trim( (string) $out ), "\n" ) );
	return json_decode( $line, true ) ?: array();
}
$L = array( array( 1, 10, 10000 ), array( 2, 11, 5000 ) );
$f = scenario( array( 'opts' => $base, 'lines' => $L ) );
t( 'transferencia elegida: un cargo negativo de 1500', array( count( $f ), $f[0]['amount'] ?? null ), array( 1, -1500 ) );
t( 'nombre incluye el porcentaje', $f[0]['name'] ?? '', 'Descuento por transferencia (10%)' );
t( 'cargo gravado con impuestos activos', $f[0]['taxable'] ?? null, true );
t( 'sin impuestos activos: no gravado', scenario( array( 'opts' => $base, 'lines' => $L, 'tax' => false ) )[0]['taxable'] ?? null, false );
t( 'otro método (Mercado Pago): sin descuento', scenario( array( 'opts' => $base, 'lines' => $L, 'chosen' => 'woo-mercado-pago-basic' ) ), array() );
t( 'fuera del checkout (carrito): sin descuento', scenario( array( 'opts' => $base, 'lines' => $L, 'checkout' => false ) ), array() );
t( 'desactivado: sin descuento', scenario( array( 'opts' => array_merge( $base, array( 'enabled' => 0 ) ), 'lines' => $L ) ), array() );
t( 'solo categoría 20', scenario( array( 'opts' => array_merge( $base, array( 'scope' => 'categories', 'categories' => array( 20 ) ) ), 'lines' => $L ) )[0]['amount'] ?? null, -1000 );
t( 'solo productos (variación -> padre)', scenario( array( 'opts' => array_merge( $base, array( 'scope' => 'products', 'products' => array( 11 ) ) ), 'lines' => array( array( 99, 11, 5000 ) ) ) )[0]['amount'] ?? null, -500 );
t( 'plugin de descuentos activo: no se aplica', scenario( array( 'opts' => $base, 'lines' => $L, 'plugins' => array( 'payment-gateway-based-fees-and-discounts-for-woocommerce/main.php' ) ) ), array() );
t( 'plugin activo + confirmación: se aplica', count( scenario( array( 'opts' => array_merge( $base, array( 'confirm_no_overlap' => 1 ) ), 'lines' => $L, 'plugins' => array( 'x/gateway-based-fees.php' ) ) ) ), 1 );
t( 'el envío no entra: solo cuentan las líneas de producto', scenario( array( 'opts' => $base, 'lines' => array( array( 1, 10, 10000 ) ) ) )[0]['amount'] ?? null, -1000 );

echo "Sanitización de ajustes\n";
$s = FanDeco_Transfer_Discount::sanitize( array( 'enabled' => '1', 'percent' => '250', 'scope' => 'hack', 'gateways' => array( 'bacs', '<b>x</b>' ), 'categories' => array( '5', 'abc', '-7' ), 'products' => 'no-array' ) );
t( 'porcentaje limitado', $s['percent'], 99.99 );
t( 'alcance inválido -> all', $s['scope'], 'all' );
t( 'gateways saneados (sin símbolos)', $s['gateways'], array( 'bacs', 'bxb' ) );
t( 'categorías: solo enteros positivos', $s['categories'], array( 5, 7 ) );
t( 'productos no-array -> vacío', $s['products'], array() );

echo $fail ? "\n$fail fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
