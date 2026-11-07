<?php
/**
 * Cálculo y formato PUROS de cuotas / precio con transferencia (testeables, sin WordPress).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || defined( 'FANDECO_CORE_TEST' ) || exit;

final class FanDeco_Installments_Calc {

	public static function per_installment( float $price, int $n, int $decimals = 2 ): float {
		return $n < 1 ? 0.0 : round( $price / $n, $decimals );
	}

	public static function transfer_price( float $price, float $percent, int $decimals = 2 ): float {
		$percent = FanDeco_Transfer_Calc::clamp_percent( $percent );
		return round( $price * ( 1 - $percent / 100 ), $decimals );
	}

	/** Importe con el formato de moneda de WooCommerce. $format usa %1$s = símbolo, %2$s = número. */
	public static function money( float $value, array $fmt ): string {
		$number = number_format( $value, (int) $fmt['dec'], (string) $fmt['ds'], (string) $fmt['ts'] );
		return sprintf( (string) $fmt['format'], (string) $fmt['sym'], $number );
	}
}
