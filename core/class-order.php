<?php
/**
 * Order
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use WC_Meta_Data;
use WC_Order;
use WC_Order_Item_Shipping;

/**
 * Class Order
 *
 * @package Nova_Poshta\Core
 */
class Order {

	/**
	 * API for Nova Poshta
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Order constructor.
	 *
	 * @param API $api API for Nova Poshta.
	 */
	public function __construct( API $api ) {
		$this->api = $api;
	}

	/**
	 * Update nonce for new user after login
	 */
	public function update_nonce_for_new_users() {
		$nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
		if ( $nonce ) {
			$_POST['woo_nova_poshta_nonce'] = wp_create_nonce( Main::PLUGIN_SLUG . '-shipping' );
		}
	}

	/**
	 * Save shipping item
	 *
	 * @param WC_Order_Item_Shipping $item        Order shipping item.
	 * @param int                    $package_key Package key.
	 * @param array                  $package     Package.
	 * @param WC_Order               $order       Current order.
	 */
	public function save( WC_Order_Item_Shipping $item, int $package_key, array $package, WC_Order $order ) {
		if ( empty( $_POST['woo_nova_poshta_nonce'] ) ) {
			return;
		}
		$nonce = filter_var( wp_unslash( $_POST['woo_nova_poshta_nonce'] ), FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		if ( 'woo_nova_poshta' !== $item->get_method_id() ) {
			return;
		}
		$city_id      = filter_input( INPUT_POST, 'woo_nova_poshta_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'woo_nova_poshta_warehouse', FILTER_SANITIZE_STRING );
		if ( ! $city_id || ! $warehouse_id ) {
			return;
		}
		$item->add_meta_data( 'city_id', $city_id, true );
		$item->add_meta_data( 'warehouse_id', $warehouse_id, true );
		$this->create_internet_document( $item, $order );
	}

	/**
	 * Create internet document
	 *
	 * @param WC_Order_Item_Shipping $item  Order shipping item.
	 * @param WC_Order               $order Current order.
	 */
	private function create_internet_document( WC_Order_Item_Shipping $item, WC_Order $order ) {
		$internet_document = $this->api->internet_document(
			$order->get_billing_first_name(),
			$order->get_billing_last_name(),
			$order->get_billing_phone(),
			$item->get_meta( 'city_id' ),
			$item->get_meta( 'warehouse_id' ),
			$order->get_total(),
			$this->order_items_quantity( $order )
		);
		$item->add_meta_data( 'internet_document', $internet_document, true );
	}

	/**
	 * Rename default labels
	 *
	 * @param string       $key  Label.
	 * @param WC_Meta_Data $meta Meta data.
	 *
	 * @return string
	 */
	public function labels( string $key, WC_Meta_Data $meta ): string {
		if ( 'city_id' === $meta->__get( 'key' ) ) {
			$key = __( 'City', 'woo-nova-poshta' );
		} elseif ( 'warehouse_id' === $meta->__get( 'key' ) ) {
			$key = __( 'Warehouse', 'woo-nova-poshta' );
		} elseif ( 'internet_document' === $meta->__get( 'key' ) ) {
			$key = __( 'Invoice', 'woo-nova-poshta' );
		}

		return $key;
	}

	/**
	 * Rename default values
	 *
	 * @param string       $value Value.
	 * @param WC_Meta_Data $meta  Meta data.
	 *
	 * @return string
	 */
	public function values( string $value, WC_Meta_Data $meta ): string {
		if ( 'city_id' === $meta->__get( 'key' ) && $meta->__get( 'value' ) ) {
			$value = $this->api->city( $meta->__get( 'value' ) );
		} elseif ( 'warehouse_id' === $meta->__get( 'key' ) && $meta->__get( 'value' ) ) {
			$value = $this->api->warehouse( $meta->__get( 'value' ) );
		}

		return $value;
	}

	/**
	 * Order items quantity
	 *
	 * @param WC_Order $order Current order.
	 *
	 * @return int
	 */
	private function order_items_quantity( WC_Order $order ): int {
		$items = $order->get_items();
		$count = 0;
		foreach ( $items as $item ) {
			$count += $item->get_quantity();
		}

		return $count;
	}

}


