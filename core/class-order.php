<?php

namespace Nova_Poshta\Core;

class Order {

	private $api;

	public function __construct( API $api ) {
		$this->api = $api;
	}

	public function edit_woocommerce_checkout_page( $order ) {
		global $post_id;
		$order = new \WC_Order( $post_id );
		echo '<p><strong>' . __( 'Field Value' ) . ':</strong> ' . get_post_meta( $order->get_id(), '_shipping_field_value', true ) . '</p>';
	}

	public function save_fields( $item, $package_key, $package, \WC_Order $order ) {
		$nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		$city      = filter_input( INPUT_POST, 'woo_nova_poshta_city', FILTER_SANITIZE_STRING );
		$warehouse = filter_input( INPUT_POST, 'woo_nova_poshta_warehouse', FILTER_SANITIZE_STRING );
		$item->add_meta_data( 'city', $city, true );
		$item->add_meta_data( 'warehouse', $warehouse, true );

		$internet_document = $this->api->internet_document(
			$order->get_billing_first_name(),
			$order->get_billing_last_name(),
			$order->get_billing_phone(),
			$city,
			$warehouse,
			$order->get_total(),
			$this->get_order_quanity( $order )
		);
		$item->add_meta_data( 'internet_document', $internet_document, true );

		$current_user_id = get_current_user_id();
		if ( $current_user_id ) {
			update_user_meta( $current_user_id, 'woo_nova_poshta_city', $city );
			update_user_meta( $current_user_id, 'woo_nova_poshta_warehouse', $warehouse );
		}
	}

	public function modify_labels( string $key, \WC_Meta_Data $meta ) {
		if ( 'city' === $meta->__get( 'key' ) ) {
			$key = __( 'City', 'woo-nova-poshta' );
		} elseif ( 'warehouse' === $meta->__get( 'key' ) ) {
			$key = __( 'Warehouse', 'woo-nova-poshta' );
		} elseif ( 'internet_document' === $meta->__get( 'key' ) ) {
			$key = __( 'Invoice', 'woo-nova-poshta' );
		}

		return $key;
	}

	public function modify_values( string $value, \WC_Meta_Data $meta ) {
		if ( 'city' === $meta->__get( 'key' ) && $meta->__get( 'value' ) ) {
			$value = $this->api->city( $meta->__get( 'value' ) );
		} elseif ( 'warehouse' === $meta->__get( 'key' ) && $meta->__get( 'value' ) ) {
			$value = $this->api->warehouse( $meta->__get( 'value' ) );
		}

		return $value;
	}

	private function get_order_quanity( \WC_Order $order ): int {
		$items = $order->get_items();
		$count = 0;
		foreach ( $items as $item ) {
			$count += $item->get_quantity();
		}

		return $count;
	}

}


