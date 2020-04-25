<?php
/**
 * Thank you page customize
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use WC_Order;

/**
 * Class Thank_You
 *
 * @package Nova_Poshta\Core
 */
class Thank_You {

	/**
	 * API for Nova Poshta
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Thank_You constructor.
	 *
	 * @param API $api API for Nova Poshta.
	 */
	public function __construct( API $api ) {
		$this->api = $api;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_filter( 'woocommerce_get_order_item_totals', [ $this, 'shipping' ], 10, 2 );
	}

	/**
	 * Modify shipping information on thank you page
	 *
	 * @param array    $total_rows Total rows on thank you page.
	 * @param WC_Order $order      Current order.
	 *
	 * @return array
	 */
	public function shipping( array $total_rows, WC_Order $order ) {
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return $total_rows;
		}

		$shipping_method = array_shift( $shipping_methods );
		if ( 'shipping_nova_poshta_for_woocommerce' !== $shipping_method->get_method_id() ) {
			return $total_rows;
		}

		$city_id      = $shipping_method->get_meta( 'city_id' );
		$warehouse_id = $shipping_method->get_meta( 'warehouse_id' );
		if ( ! $city_id || ! $warehouse_id ) {
			return $total_rows;
		}
		$city      = $this->api->city( $city_id );
		$warehouse = $this->api->warehouse( $warehouse_id );

		$total_rows['shipping']['value'] .= sprintf( '<br>%s<br>%s', $city, $warehouse );

		$internet_document = $shipping_method->get_meta( 'internet_document' );
		if ( ! $internet_document ) {
			return $total_rows;
		}
		$total_rows['shipping']['value'] .= sprintf( '<br>%s', $internet_document );

		return $total_rows;
	}

}
