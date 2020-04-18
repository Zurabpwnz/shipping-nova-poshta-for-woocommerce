<?php
/**
 * Checkout
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
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
		if ( 'woo_nova_poshta' === $shipping_method && 'woo_nova_poshta' === $shipping_rate->get_method_id() ) {
			do_action( 'woo_nova_poshta_user_fields' );
		}
	}

	/**
	 * Validate fields
	 */
	public function validate() {
		$nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}
		if ( isset( $_POST['woo_nova_poshta_city'] ) && empty( $_POST['woo_nova_poshta_city'] ) ) {
			wc_add_notice( __( 'Select delivery city', 'woo-nova-poshta' ), 'error' );
		}
		if ( isset( $_POST['woo_nova_poshta_warehouse'] ) && empty( $_POST['woo_nova_poshta_warehouse'] ) ) {
			wc_add_notice( __( 'Choose branch', 'woo-nova-poshta' ), 'error' );
		}
	}

}
