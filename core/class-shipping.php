<?php

namespace Nova_Poshta\Core;

class Shipping {

	private $api;

	public function __construct( API $api ) {
		$this->api         = $api;
	}

	public function require_methods() {
		require_once plugin_dir_path( __DIR__ ) . 'shipping/class-nova-poshta-shipping-method.php';
	}

	public function register_methods( array $methods ): array {
		$methods['woo_nova_poshta'] = 'Nova_Poshta_Shipping_Method';

		return $methods;
	}

	public function add_fields( $method ) {
		$shipping_method = filter_input( INPUT_POST, 'shipping_method', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$shipping_method = isset( $shipping_method[0] ) ? preg_replace( '/:[0-9]{1,10}$/', '', $shipping_method[0] ) : [];
		if ( 'woo_nova_poshta' === $shipping_method && 'woo_nova_poshta' === $method->method_id ) {
			do_action( 'woo_nova_poshta_user_fields' );
		}
	}

	public function validate(): void {
		$nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		if ( isset( $_POST['woo_nova_poshta_city'] ) && empty( $_POST['woo_nova_poshta_city'] ) ) {
			wc_add_notice( __( 'Select a city for delivery', 'woo-nova-poshta' ), 'error' );
		}
		if ( isset( $_POST['woo_nova_poshta_warehouse'] ) && empty( $_POST['woo_nova_poshta_warehouse'] ) ) {
			wc_add_notice( __( 'Choose a branch for delivery', 'woo-nova-poshta' ), 'error' );
		}
	}

	public function default_city( string $city ): string {
		$current_user_id = get_current_user_id();
		if ( $current_user_id ) {
			$user_city = get_user_meta( $current_user_id, 'woo_nova_poshta_city', true );
		}

		return ! empty( $user_city ) ? $user_city : $city;
	}

	public function default_warehouse( string $warehouse ): string {
		$current_user_id = get_current_user_id();
		if ( $current_user_id ) {
			$user_warehouse = get_user_meta( $current_user_id, 'woo_nova_poshta_warehouse', true );
		}

		return ! empty( $user_warehouse ) ? $user_warehouse : $warehouse;
	}

}
