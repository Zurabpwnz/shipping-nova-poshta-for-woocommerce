<?php
/**
 * Order
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
use WC_Meta_Data;
use WC_Order_Item;
use WC_Data_Exception;
use WC_Order_Item_Shipping;
use Nova_Poshta_Gateway_COD;

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
	 * Calculate a shipping cost
	 *
	 * @var Shipping_Cost
	 */
	private $shipping_cost;
	/**
	 * Internet document
	 *
	 * @var Internet_Document
	 */
	private $internet_document;

	/**
	 * Order constructor.
	 *
	 * @param API               $api               API for Nova Poshta.
	 * @param Shipping_Cost     $shipping_cost     Calculate a shipping cost.
	 * @param Internet_Document $internet_document Internet document.
	 */
	public function __construct( API $api, Shipping_Cost $shipping_cost, Internet_Document $internet_document ) {
		$this->api               = $api;
		$this->shipping_cost     = $shipping_cost;
		$this->internet_document = $internet_document;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $this, 'create' ], 10, 4 );
		add_action( 'woocommerce_before_order_item_object_save', [ $this, 'save' ] );
		add_action( 'woocommerce_checkout_update_customer', [ $this, 'update_nonce_for_new_users' ] );
		add_action( 'woocommerce_order_actions', [ $this, 'register_order_actions' ] );
		add_action(
			'woocommerce_order_action_nova_poshta_create_internet_document',
			[
				$this,
				'create_internet_document',
			]
		);
		add_action( 'woocommerce_before_order_itemmeta', [ $this, 'default_fields_for_shipping_item' ], 10, 2 );

		add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'labels' ], 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'values' ], 10, 2 );
	}

	/**
	 * Update nonce for new user after login
	 */
	public function update_nonce_for_new_users() {
		$nonce = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_nonce', FILTER_SANITIZE_STRING );
		if ( $nonce ) {
			$_POST['shipping_nova_poshta_for_woocommerce_nonce'] = wp_create_nonce( Main::PLUGIN_SLUG . '-shipping' );
		}
	}

	/**
	 * Save shipping item
	 *
	 * @param WC_Order_Item_Shipping $item        Order shipping item.
	 * @param int                    $package_key Package key.
	 * @param array                  $package     Package.
	 * @param WC_Order               $order       Current order.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function create( WC_Order_Item_Shipping $item, int $package_key, array $package, WC_Order $order ) {
		if ( empty( $_POST['shipping_nova_poshta_for_woocommerce_nonce'] ) ) {
			return;
		}
		$nonce = filter_var( wp_unslash( $_POST['shipping_nova_poshta_for_woocommerce_nonce'] ), FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		if ( 'shipping_nova_poshta_for_woocommerce' !== $item->get_method_id() ) {
			return;
		}
		$city_id      = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_warehouse', FILTER_SANITIZE_STRING );
		if ( ! $city_id || ! $warehouse_id ) {
			return;
		}
		$item->add_meta_data( 'city_id', $city_id, true );
		$item->add_meta_data( 'warehouse_id', $warehouse_id, true );
	}

	/**
	 * Save shipping method
	 *
	 * @param WC_Order_Item $order_item WC Order Item.
	 *
	 * @throws WC_Data_Exception Invalid total shipping cost.
	 * @throws Exception Invalid DateTime.
	 */
	public function save( WC_Order_Item $order_item ) {
		if ( ! is_admin() ) {
			return;
		}
		if ( ! is_a( $order_item, 'WC_Order_Item_Shipping' ) ) {
			return;
		}
		if ( 'shipping_nova_poshta_for_woocommerce' !== $order_item->get_method_id() ) {
			return;
		}
		$city_id = $order_item->get_meta( 'city_id', true );
		if ( ! $city_id ) {
			return;
		}
		$order = $order_item->get_order();
		$items = $order->get_items();
		if ( empty( $items ) ) {
			return;
		}
		$products = [];
		foreach ( $items as $item ) {
			$products[] = [
				'quantity' => $item->get_quantity(),
				'data'     => $item->get_product(),
			];
		}
		$order_item->set_total(
			(int) $this->shipping_cost->calculate( $city_id, $products )
		);
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
			$key = __( 'City', 'shipping-nova-poshta-for-woocommerce' );
		} elseif ( 'warehouse_id' === $meta->__get( 'key' ) ) {
			$key = __( 'Warehouse', 'shipping-nova-poshta-for-woocommerce' );
		} elseif ( 'internet_document' === $meta->__get( 'key' ) ) {
			$key = __( 'Invoice', 'shipping-nova-poshta-for-woocommerce' );
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
	 * @param int           $item_id Item ID.
	 * @param WC_Order_Item $item    Item.
	 */
	public function default_fields_for_shipping_item( int $item_id, WC_Order_Item $item ) {
		if ( ! is_a( $item, 'WC_Order_Item_Shipping' ) ) {
			return;
		}
		if ( 'shipping_nova_poshta_for_woocommerce' !== $item->get_method_id() ) {
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
		global $post;
		$order = wc_get_order( $post->ID );
		if ( Nova_Poshta_Gateway_COD::ID === $order->get_payment_method() && 'pending' === $order->get_status() ) {
			return $actions;
		}
		$actions['nova_poshta_create_internet_document'] = __( 'Create Nova Poshta Internet Document', 'shipping-nova-poshta-for-woocommerce' );

		return $actions;
	}

	/**
	 * Create internet document
	 *
	 * @param WC_Order $order Current order.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function create_internet_document( WC_Order $order ) {
		$this->internet_document->create( $order );
	}

}


