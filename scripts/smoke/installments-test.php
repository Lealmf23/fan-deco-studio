<?php
/** Pruebas de "Precio y cuotas": cálculo, HTML por escenario (subprocesos) y formato. */
define( 'FANDECO_CORE_TEST', true );
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_CORE_URL', '/p/' );
define( 'FANDECO_CORE_VERSION', 't' );

$G = array( 'inst' => array(), 'transfer' => array(), 'plugins' => array(), 'tax' => false, 'cats' => array( 10 => array( 20 ), 11 => array( 21 ) ), 'pos' => '%1$s&nbsp;%2$s' );
if ( isset( $argv[1] ) ) { $G = array_replace( $G, json_decode( base64_decode( $argv[1] ), true ) ); }
function add_action() {} function add_filter() {} function add_shortcode() {}
function get_option( $k, $d = false ) { global $G; return array( 'fandeco_core_installments' => $G['inst'], 'fandeco_core_transfer' => $G['transfer'], 'active_plugins' => $G['plugins'] )[ $k ] ?? $d; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_html__( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function wp_json_encode( $v ) { return json_encode( $v, JSON_UNESCAPED_UNICODE ); }
function get_woocommerce_currency_symbol() { return '&#36;'; }
function get_woocommerce_price_format() { global $G; return $G['pos']; }
function wc_get_price_decimals() { return 2; }
function wc_get_price_decimal_separator() { return ','; }
function wc_get_price_thousand_separator() { return '.'; }
function wc_tax_enabled() { global $G; return $G['tax']; }
function wc_get_price_to_display( $p ) { return $p->price; }
function wc_get_product_term_ids( $pid, $t ) { global $G; return $G['cats'][ $pid ] ?? array(); }
function get_ancestors() { return array(); }
function WC() { return null; }
function is_checkout() { return false; }
function wc_get_product( $id ) { return $GLOBALS['catalog'][ $id ] ?? null; }
class WC_Product { public function __construct( public int $id, public float $price, public int $parent = 0, public bool $buy = true ) {} function get_id() { return $this->id; } function get_parent_id() { return $this->parent; } function is_purchasable() { return $this->buy; } }
class WC_Product_Variable extends WC_Product { public array $kids = array(); public array $range = array( 0, 0 ); function get_children() { return $this->kids; } function get_variation_price( $w, $d ) { return 'min' === $w ? $this->range[0] : $this->range[1]; } }
$catalog = array();
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-transfer-calc.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-transfer-discount.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-installments-calc.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-installments.php';

if ( isset( $argv[1] ) ) { // modo subproceso: imprime el HTML del producto pedido
	$spec = $G['product'];
	if ( ! empty( $spec['variable'] ) ) {
		$p = new WC_Product_Variable( 10, 0 ); $p->range = $spec['range']; $p->kids = array( 101, 102 );
		$catalog[101] = new WC_Product( 101, $spec['range'][0], 10 ); $catalog[102] = new WC_Product( 102, $spec['range'][1], 10 );
	} else { $p = new WC_Product( $spec['id'] ?? 10, (float) $spec['price'], 0, $spec['buy'] ?? true ); }
	echo FanDeco_Installments::html( $p, 'single' );
	exit;
}

$fail = 0;
function t( string $n, $got, $want ): void { global $fail; $ok = $got === $want; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $n . ( $ok ? '' : "\n         got:  " . var_export( $got, true ) . "\n         want: " . var_export( $want, true ) ) . "\n"; if ( ! $ok ) { ++$fail; } }
function html( array $g ): string { return (string) shell_exec( 'php ' . escapeshellarg( __FILE__ ) . ' ' . escapeshellarg( base64_encode( json_encode( $g ) ) ) ); }
$on  = array( 'enabled' => 1, 'installments' => 3, 'show_single' => 1, 'show_transfer' => 1, 'min_total' => 0 );
$tr  = array( 'enabled' => 1, 'percent' => 10, 'scope' => 'all', 'taxable' => 1, 'confirm_no_overlap' => 0 );

echo "Cálculo y formato\n";
t( 'cuota = precio / n', FanDeco_Installments_Calc::per_installment( 15000, 3 ), 5000.0 );
t( 'cuota redondeada', FanDeco_Installments_Calc::per_installment( 10000, 3 ), 3333.33 );
t( 'cuotas n=0 no divide por cero', FanDeco_Installments_Calc::per_installment( 100, 0 ), 0.0 );
t( 'precio con transferencia', FanDeco_Installments_Calc::transfer_price( 15000, 10 ), 13500.0 );
$fmt = array( 'sym' => '$', 'format' => '%1$s%2$s', 'dec' => 2, 'ds' => ',', 'ts' => '.' );
t( 'formato argentino', FanDeco_Installments_Calc::money( 1234567.5, $fmt ), '$1.234.567,50' );

echo "HTML por escenario\n";
$h = html( array( 'inst' => $on, 'transfer' => $tr, 'product' => array( 'price' => 15000 ) ) );
t( '3 cuotas de $5.000,00', str_contains( $h, '3 cuotas sin interés de <strong data-fd-inst-amount>$&nbsp;5.000,00</strong>' ) || str_contains( $h, '3 cuotas sin interés de <strong data-fd-inst-amount>$' . "\xc2\xa0" . '5.000,00</strong>' ), true );
t( 'transferencia $13.500,00 con 10% OFF', str_contains( $h, '13.500,00' ) && str_contains( $h, '10% OFF' ) && ! str_contains( $h, 'data-fd-transfer-line hidden' ), true );
t( 'desactivado: nada', html( array( 'inst' => array_merge( $on, array( 'enabled' => 0 ) ), 'transfer' => $tr, 'product' => array( 'price' => 15000 ) ) ), '' );
t( 'descuento desactivado: línea de transferencia oculta', str_contains( html( array( 'inst' => $on, 'transfer' => array_merge( $tr, array( 'enabled' => 0 ) ), 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'plugin de descuentos activo sin confirmar: oculta transferencia', str_contains( html( array( 'inst' => $on, 'transfer' => $tr, 'plugins' => array( 'x/gateway-based-fees.php' ), 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'show_transfer off: oculta transferencia', str_contains( html( array( 'inst' => array_merge( $on, array( 'show_transfer' => 0 ) ), 'transfer' => $tr, 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'alcance categorías: producto fuera -> oculta', str_contains( html( array( 'inst' => $on, 'transfer' => array_merge( $tr, array( 'scope' => 'categories', 'categories' => array( 99 ) ) ), 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'alcance categorías: producto dentro -> visible', ! str_contains( html( array( 'inst' => $on, 'transfer' => array_merge( $tr, array( 'scope' => 'categories', 'categories' => array( 20 ) ) ), 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'impuestos activos con cargo NO gravado: no se muestra precio de transferencia (evita mostrar un precio distinto al cobrado)', str_contains( html( array( 'inst' => $on, 'transfer' => array_merge( $tr, array( 'taxable' => 0 ) ), 'tax' => true, 'product' => array( 'price' => 15000 ) ) ), 'data-fd-transfer-line hidden' ), true );
t( 'importe mínimo: oculta cuotas bajo el mínimo', str_contains( html( array( 'inst' => array_merge( $on, array( 'min_total' => 20000 ) ), 'transfer' => $tr, 'product' => array( 'price' => 15000 ) ) ), 'data-fd-inst-line hidden' ), true );
t( 'producto no comprable: nada', html( array( 'inst' => $on, 'transfer' => $tr, 'product' => array( 'price' => 15000, 'buy' => false ) ) ), '' );
t( 'precio 0: nada', html( array( 'inst' => $on, 'transfer' => $tr, 'product' => array( 'price' => 0 ) ) ), '' );
$v = html( array( 'inst' => $on, 'transfer' => $tr, 'product' => array( 'variable' => true, 'range' => array( 9000, 15000 ) ) ) );
t( 'variable con rango: prefijo "Desde" y data-from=1', str_contains( $v, 'data-fd-inst-prefix' ) && str_contains( $v, 'data-from="1"' ), true );
t( 'variable: la línea de transferencia también lleva "Desde"', substr_count( $v, 'data-fd-inst-prefix' ) === 2, true );
t( 'variable usa el precio mínimo (3.000,00)', str_contains( $v, '3.000,00' ), true );
t( 'escapa atributos (data-fmt JSON válido)', is_array( json_decode( html_entity_decode( ( preg_match( '/data-fmt="([^"]*)"/', $v, $m ) ? $m[1] : '' ), ENT_QUOTES ), true ) ), true );
$sym = html( array( 'inst' => $on, 'transfer' => $tr, 'pos' => '%2$s%1$s', 'product' => array( 'price' => 15000 ) ) );
t( 'respeta la posición del símbolo (derecha)', str_contains( $sym, '5.000,00$' ), true );

echo $fail ? "\n$fail fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
