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

use WC_Order_Item_Product;

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
	 * Calculation formula
	 *
	 * @var Calculation
	 */
	private $calculation;

	public function __construct( API $api, Settings $settings, Calculation $calculation ) {
		$this->api         = $api;
		$this->settings    = $settings;
		$this->calculation = $calculation;
	}

	public function calculate( string $recipient_city_id ) {
		global $woocommerce;
		$cart = $woocommerce->cart;
		var_dump( $cart->get_cart_contents() );
//		$items  = '???';
//		$weight = 0;
//		$volume = 0;
//		foreach ( $items as $item ) {
//			$weight += $this->get_weight( $item );
//			$volume += $this->get_volume( $item );
//		}
//		$this->api->shipping_cost( $recipient_city_id, $this->get_weight(), $this->get_volume() );
	}

	private function get_weight( WC_Order_Item_Product $item ): float {
		$formula = $this->get_current_formula( $item, 'weight' );

		return $this->calculation->result( $formula, $item->get_quantity() );
	}

	private function get_volume( WC_Order_Item_Product $item ): float {
		$width   = $this->get_current_formula( $item, 'width' );
		$length  = $this->get_current_formula( $item, 'length' );
		$height  = $this->get_current_formula( $item, 'height' );
		$formula = '(' . $width . ') * (' . $length . ') * (' . $height . ')';

		return $this->calculation->result( $formula, $item->get_quantity() );
	}

	private function get_current_formula( WC_Order_Item_Product $item, string $key ) {
		$product = $item->get_product();
		$weight  = $product->get_meta( 'default_' . $key );
		if ( $weight ) {
			return '';
		}
		$category_id = ! empty( $product->get_category_ids()[0] ) ? $product->get_category_ids()[0] : 0;
		if ( $category_id ) {
			$weight = get_term_meta( $category_id, 'default_' . $key, true );
			if ( $weight ) {
				return '';
			}
		}

		return $this->settings->default_{$key}();
	}

}
