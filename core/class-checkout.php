<?php
/**
 * Checkout
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use WC_Shipping_Rate;

/**
 * Class Shipping
 *
 * @package Nova_Poshta\Core
 */
class Checkout {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_after_shipping_rate', [ $this, 'fields' ] );
		add_action( 'woocommerce_checkout_process', [ $this, 'validate' ] );
	}

	/**
	 * Fields
	 *
	 * @param WC_Shipping_Rate $shipping_rate Shipping rate.
	 */
	public function fields( WC_Shipping_Rate $shipping_rate ) {
		$shipping_method = filter_input( INPUT_POST, 'shipping_method', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$shipping_method = isset( $shipping_method[0] ) ? preg_replace( '/:[0-9]{1,10}$/', '', $shipping_method[0] ) : [];
		if ( 'shipping_nova_poshta_for_woocommerce' === $shipping_method && 'shipping_nova_poshta_for_woocommerce' === $shipping_rate->get_method_id() ) {
			do_action( 'shipping_nova_poshta_for_woocommerce_user_fields' );
		}
	}

	/**
	 * Validate fields
	 */
	public function validate() {
		$nonce = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		if ( isset( $_POST['shipping_nova_poshta_for_woocommerce_city'] ) && empty( $_POST['shipping_nova_poshta_for_woocommerce_city'] ) ) {
			wc_add_notice( __( 'Select delivery city', 'shipping-nova-poshta-for-woocommerce' ), 'error' );
		}
		if ( isset( $_POST['shipping_nova_poshta_for_woocommerce_warehouse'] ) && empty( $_POST['shipping_nova_poshta_for_woocommerce_warehouse'] ) ) {
			wc_add_notice( __( 'Choose branch', 'shipping-nova-poshta-for-woocommerce' ), 'error' );
		}
	}

}
