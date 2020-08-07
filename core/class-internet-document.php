<?php
/**
 * Internet_Document
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
use Exception;
use WC_Order_Item_Shipping;
use Nova_Poshta_Gateway_COD;
use Nova_Poshta\Admin\Notice\Notice;

/**
 * Class Internet_Document
 *
 * @package Nova_Poshta\Core
 */
class Internet_Document {

	/**
	 * API for Nova Poshta
	 *
	 * @var API
	 */
	private $api;
	/**
	 * Calculate a shipping cost
	 *
	 * @var Shipping_Cost
	 */
	private $shipping_cost;
	/**
	 * Notice
	 *
	 * @var Notice
	 */
	private $notice;

	/**
	 * Order constructor.
	 *
	 * @param API           $api           API for Nova Poshta.
	 * @param Shipping_Cost $shipping_cost Calculate a shipping cost.
	 * @param Notice        $notice        Notice.
	 */
	public function __construct( API $api, Shipping_Cost $shipping_cost, Notice $notice ) {
		$this->api           = $api;
		$this->shipping_cost = $shipping_cost;
		$this->notice        = $notice;
	}

	/**
	 * Create internet document for WC_Order
	 *
	 * @param WC_Order $order WooCommerce order.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function create( WC_Order $order ) {
		$shipping_item = $this->find_shipping_method( $order->get_shipping_methods() );
		if ( ! $shipping_item ) {
			return;
		}
		if ( $shipping_item->get_meta( 'internet_document' ) ) {
			$this->notice->add( 'error', __( 'The invoice was created before', 'shipping-nova-poshta-for-woocommerce' ) );

			return;
		}

		$items = $order->get_items();
		if ( empty( $items ) ) {
			$this->notice->add( 'error', __( 'The order doesn\'t have a products', 'shipping-nova-poshta-for-woocommerce' ) );

			return;
		}
		$products = [];
		foreach ( $items as $item ) {
			$products[] = [
				'quantity' => $item->get_quantity(),
				'data'     => $item->get_product(),
			];
		}
		$weight = $this->shipping_cost->get_products_weight( $products );
		$volume = $this->shipping_cost->get_products_volume( $products );

		$prepayment = 0;
		if ( Nova_Poshta_Gateway_COD::ID === $order->get_payment_method() ) {
			$options    = get_option( Nova_Poshta_Gateway_COD::ID . '_settings' );
			$prepayment = $options['prepayment'] ? $options['prepayment'] : 0;
		}

		$total = $order->get_total() - $order->get_shipping_total();

		$internet_document = $this->api->internet_document(
			$order->get_billing_first_name(),
			$order->get_billing_last_name(),
			$order->get_billing_phone(),
			$shipping_item->get_meta( 'city_id' ),
			$shipping_item->get_meta( 'warehouse_id' ),
			$total,
			$weight,
			$volume,
			$prepayment ? $total - $prepayment : 0
		);
		if ( $internet_document ) {
			$shipping_item->add_meta_data( 'internet_document', $internet_document, true );
			$shipping_item->save_meta_data();
			$this->notice->add( 'success', __( 'The invoice will successfully create', 'shipping-nova-poshta-for-woocommerce' ) );
			$order->add_order_note(
				__( 'Created Internet document for Nova Poshta', 'shipping-nova-poshta-for-woocommerce' )
			);
		} else {
			$this->notice->add( 'error', __( 'The invoice wasn\'t created because:', 'shipping-nova-poshta-for-woocommerce' ) );
			$errors = $this->api->errors();
			foreach ( $errors as $error ) {
				$this->notice->add( 'error', $error );
			}
		}
	}

	/**
	 * Find current shipping method
	 *
	 * @param array $shipping_methods List of shipping methods.
	 *
	 * @return WC_Order_Item_Shipping|null
	 */
	private function find_shipping_method( array $shipping_methods ) {
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'shipping_nova_poshta_for_woocommerce' === $shipping_method->get_method_id() ) {
				return $shipping_method;
			}
		}

		return null;
	}

}
