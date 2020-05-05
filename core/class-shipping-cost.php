<?php
/**
 * Calculate shipping cost
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use Exception;
use WC_Product;

/**
 * Class Shipping_Cost
 *
 * @package Nova_Poshta\Core
 */
class Shipping_Cost {

	/**
	 * API
	 *
	 * @var API
	 */
	private $api;
	/**
	 * Settings
	 *
	 * @var Settings
	 */
	private $settings;
	/**
	 * Calculator
	 *
	 * @var Calculator
	 */
	private $calculator;

	/**
	 * Shipping_Cost constructor.
	 *
	 * @param API        $api        API.
	 * @param Settings   $settings   Plugin settings.
	 * @param Calculator $calculator Calculator of math strings.
	 */
	public function __construct( API $api, Settings $settings, Calculator $calculator ) {
		$this->api        = $api;
		$this->settings   = $settings;
		$this->calculator = $calculator;
	}

	/**
	 * Calculate shipping cost
	 *
	 * @param string $recipient_city_id Recipient city ID.
	 *
	 * @return float
	 * @throws Exception Invalid DateTime.
	 */
	public function calculate( string $recipient_city_id ): float {
		global $woocommerce;
		$cart   = $woocommerce->cart;
		$items  = $cart->get_cart_contents();
		$weight = 0;
		$volume = 0;
		foreach ( $items as $item ) {
			$weight += $this->get_weight( $item['data'], $item['quantity'] );
			$volume += $this->get_volume( $item['data'], $item['quantity'] );
		}

		return $this->api->shipping_cost( $recipient_city_id, $weight, $volume );
	}

	/**
	 * Get products weight
	 *
	 * @param WC_Product $product  Product.
	 * @param int        $quantity Quantity of this products.
	 *
	 * @return float
	 */
	private function get_weight( WC_Product $product, int $quantity ): float {
		$formula = $this->get_current_formula( $product, 'weight' );

		return $this->calculator->result( $formula, $quantity );
	}

	/**
	 * Get products volume
	 *
	 * @param WC_Product $product  Product.
	 * @param int        $quantity Quantity of this products.
	 *
	 * @return int
	 */
	private function get_volume( WC_Product $product, int $quantity ): int {
		$width   = $this->get_current_formula( $product, 'width' );
		$length  = $this->get_current_formula( $product, 'length' );
		$height  = $this->get_current_formula( $product, 'height' );
		$formula = '(' . $width . ') * (' . $length . ') * (' . $height . ')';

		return $this->calculator->result( $formula, $quantity );
	}

	/**
	 * Get current formula.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $key     Key.
	 *
	 * @return string
	 */
	private function get_current_formula( WC_Product $product, string $key ): string {
		// Get parent product for variation products.
		$product = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
		$formula = $product->get_meta( 'default_' . $key . '_formula' );
		if ( $formula ) {
			return $formula;
		}
		$category_id = ! empty( $product->get_category_ids()[0] ) ? $product->get_category_ids()[0] : 0;
		if ( $category_id ) {
			$formula = get_term_meta( $category_id, 'default_' . $key . '_formula', true );
			if ( $formula ) {
				return $formula;
			}
		}

		$method_name = 'default_' . $key . '_formula';

		return $this->settings->{$method_name}();
	}

}
