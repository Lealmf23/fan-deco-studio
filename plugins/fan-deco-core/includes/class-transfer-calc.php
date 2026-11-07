<?php
/**
 * Cálculo PURO del descuento por transferencia (sin WordPress/WooCommerce; testeable).
 *
 * Cada línea: array{product_id:int, parent_id:int, categories:int[], total:float}
 * donde `total` es el total de la línea YA con cupones aplicados, SIN envío ni cargos.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || defined( 'FANDECO_CORE_TEST' ) || exit;

final class FanDeco_Transfer_Calc {

	public static function clamp_percent( $value ): float {
		$value = (float) str_replace( ',', '.', (string) $value );
		return min( 99.99, max( 0.0, round( $value, 2 ) ) );
	}

	/** @param int[] $categories @param int[] $products */
	public static function line_eligible( array $line, string $scope, array $categories, array $products ): bool {
		switch ( $scope ) {
			case 'all':
				return true;
			case 'categories':
				return (bool) array_intersect( array_map( 'intval', $categories ), array_map( 'intval', $line['categories'] ?? array() ) );
			case 'products':
				$products = array_map( 'intval', $products );
				return in_array( (int) ( $line['product_id'] ?? 0 ), $products, true ) || in_array( (int) ( $line['parent_id'] ?? 0 ), $products, true );
		}
		return false;
	}

	/** Importe del descuento (positivo). 0 si no corresponde. */
	public static function amount( array $lines, float $percent, string $scope, array $categories, array $products, int $decimals = 2 ): float {
		$percent = self::clamp_percent( $percent );
		if ( $percent <= 0 ) {
			return 0.0;
		}
		$base = 0.0;
		foreach ( $lines as $line ) {
			if ( self::line_eligible( $line, $scope, $categories, $products ) ) {
				$base += max( 0.0, (float) ( $line['total'] ?? 0 ) );
			}
		}
		$amount = round( $base * $percent / 100, $decimals );
		return min( $amount, round( $base, $decimals ) );
	}
}
