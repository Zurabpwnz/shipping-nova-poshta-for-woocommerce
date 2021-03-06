<?php
/**
 * Calculator dimensions
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use WC_Eval_Math;

/**
 * Class Calculator
 *
 * @package Nova_Poshta\Core\Calculation
 */
class Calculator {

	/**
	 * Calculate formula
	 *
	 * @param string $formula  Math formula.
	 * @param int    $quantity Quantity of products.
	 *
	 * @return float
	 */
	public function result( string $formula, int $quantity ): float {
		if ( ! class_exists( 'WC_Eval_Math' ) ) {
			// @codeCoverageIgnoreStart
			include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';
			// @codeCoverageIgnoreEnd
		}
		$sum = str_replace( '[qty]', $quantity, $formula );
		// Remove whitespace from string.
		$sum = preg_replace( '/\s+/', '', $sum );

		$locale   = localeconv();
		$decimals = [ wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' ];

		// Remove locale from string.
		$sum = str_replace( $decimals, '.', $sum );
		// Trim invalid start/end characters.
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		// Do the math.
		return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
	}

}
