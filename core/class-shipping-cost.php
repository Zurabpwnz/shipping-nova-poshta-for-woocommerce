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
	 * @param array  $products          List of products.
	 *                                  Each product will be array with 'data' => WC_Product and quantity.
	 *
	 * @return float
	 * @throws Exception Invalid DateTime.
	 */
	public function calculate( string $recipient_city_id, array $products ): float {
		if ( ! $this->settings->is_shipping_cost_enable() ) {
			return 0;
		}
		$weight = 0;
		$volume = 0;
		foreach ( $products as $item ) {
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
		$weight = $this->get_product_weight( $product );
		if ( $weight ) {
			return $weight * $quantity;
		}

		$formula = $this->get_current_formula( $product, 'weight' );

		return $this->calculator->result( $formula, $quantity );
	}

	/**
	 * Get products volume
	 *
	 * @param WC_Product $product  Product.
	 * @param int        $quantity Quantity of this products.
	 *
	 * @return float
	 */
	private function get_volume( WC_Product $product, int $quantity ): float {
		$width   = $this->get_dimension( $product, $quantity, 'width' );
		$length  = $this->get_dimension( $product, $quantity, 'length' );
		$height  = $this->get_dimension( $product, $quantity, 'height' );
		$formula = '(' . $width . ') * (' . $length . ') * (' . $height . ')';

		return $this->calculator->result( $formula, $quantity );
	}

	/**
	 * Get dimension
	 *
	 * @param WC_Product $product        Product.
	 * @param int        $quantity       Quantity.
	 * @param string     $dimension_name Dimension name.
	 *
	 * @return float
	 */
	private function get_dimension( WC_Product $product, int $quantity, string $dimension_name ): float {
		$dimension = $this->get_product_dimension( $product, $dimension_name );
		if ( $dimension ) {
			return $dimension * $quantity;
		}

		$formula = $this->get_current_formula( $product, $dimension_name );

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
		$formula = $product->get_meta( $key . '_formula', true );
		if ( $formula ) {
			return $formula;
		}
		$category_id = ! empty( $product->get_category_ids()[0] ) ? $product->get_category_ids()[0] : 0;
		if ( $category_id ) {
			$formula = get_term_meta( $category_id, $key . '_formula', true );
			if ( $formula ) {
				return $formula;
			}
		}

		$method_name = 'default_' . $key . '_formula';

		return $this->settings->{$method_name}();
	}

	/**
	 * Get product dimension
	 *
	 * @param WC_Product $product        Product.
	 * @param string     $dimension_name Dimension name.
	 *
	 * @return float
	 */
	private function get_product_dimension( WC_Product $product, string $dimension_name ): float {
		$method    = 'get_' . $dimension_name;
		$dimension = (float) $product->{$method}();
		if ( $dimension ) {
			return wc_get_dimension( $dimension, 'm', get_option( 'woocommerce_dimension_unit' ) );
		}
		if ( $product->get_parent_id() ) {
			return $this->get_product_dimension( wc_get_product( $product->get_parent_id() ), $dimension_name );
		}

		return 0;
	}

	/**
	 * Get product weight
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return float
	 */
	private function get_product_weight( WC_Product $product ): float {
		$weight = $product->get_weight();
		if ( $weight ) {
			return wc_get_weight( (float) $weight, 'kg', get_option( 'woocommerce_weight_unit' ) );
		}
		// If in variation product haven't a weight try get in parent product.
		if ( $product->get_parent_id() ) {
			return $this->get_product_weight( wc_get_product( $product->get_parent_id() ) );
		}

		return 0;
	}

}
