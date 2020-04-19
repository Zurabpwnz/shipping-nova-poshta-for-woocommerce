<?php
/**
 * Order
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use Exception;
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
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $this, 'save' ], 10, 4 );
		add_action( 'woocommerce_checkout_update_customer', [ $this, 'update_nonce_for_new_users' ] );
		add_action( 'woocommerce_order_actions', [ $this, 'register_order_actions' ] );
		add_action(
			'woocommerce_order_action_nova_poshta_create_internet_document',
			[
				$this,
				'create_internet_document',
			]
		);
		add_action( 'woocommerce_order_status_processing', [ $this, 'processing_status' ], 10, 2 );
		add_action( 'woocommerce_before_order_itemmeta', [ $this, 'default_fields_for_shipping_item' ], 10, 2 );

		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'labels' ], 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'values' ], 10, 2 );
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
	 * Default fields for shipping item
	 *
	 * @param int            $item_id Item ID.
	 * @param \WC_Order_Item $item    Item.
	 */
	public function default_fields_for_shipping_item( int $item_id, \WC_Order_Item $item ) {
		if ( ! is_a( $item, 'WC_Order_Item_Shipping' ) ) {
			return;
		}
		if ( 'woo_nova_poshta' !== $item->get_method_id() ) {
			return;
		}
		$save = false;
		if ( ! $item->get_meta( 'city_id' ) ) {
			$city = $this->api->cities( '', 1 );
			$item->update_meta_data( 'city_id', array_keys( $city )[0] );
			$save = true;
		}
		if ( ! $item->get_meta( 'warehouse_id' ) ) {
			$city_id    = $item->get_meta( 'city_id' );
			$warehouses = $this->api->warehouses( $city_id );
			$item->update_meta_data( 'warehouse_id', array_keys( $warehouses )[0] );
			$save = true;
		}
		if ( $save ) {
			$item->save_meta_data();
		}
	}

	/**
	 * Register actions
	 *
	 * @param array $actions List of actions.
	 *
	 * @return array
	 */
	public function register_order_actions( array $actions ): array {
		$actions['nova_poshta_create_internet_document'] = __( 'Create Nova Poshta Internet Document', 'woo-nova-poshta' );

		return $actions;
	}

	/**
	 * Change status to processing
	 *
	 * @param int      $order_id Current order ID.
	 * @param WC_Order $order    Current order.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function processing_status( int $order_id, WC_Order $order ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->create_internet_document( $order );
	}

	/**
	 * Create internet document
	 *
	 * @param WC_Order $order Current order.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function create_internet_document( WC_Order $order ) {
		$shipping_item = $this->find_shipping_method( $order->get_shipping_methods() );
		if ( ! $shipping_item ) {
			return;
		}
		$internet_document = $this->api->internet_document(
			$order->get_billing_first_name(),
			$order->get_billing_last_name(),
			$order->get_billing_phone(),
			$shipping_item->get_meta( 'city_id' ),
			$shipping_item->get_meta( 'warehouse_id' ),
			$order->get_total(),
			$this->order_items_quantity( $order )
		);
		if ( $internet_document ) {
			$shipping_item->add_meta_data( 'internet_document', $internet_document, true );
			$shipping_item->save_meta_data();
		}
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

	/**
	 * Find current shipping method
	 *
	 * @param array $shipping_methods List of shipping methods.
	 *
	 * @return WC_Order_Item_Shipping|null
	 */
	private function find_shipping_method( array $shipping_methods ) {
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'woo_nova_poshta' === $shipping_method->get_method_id() ) {
				return $shipping_method;
			}
		}

		return null;
	}

}


