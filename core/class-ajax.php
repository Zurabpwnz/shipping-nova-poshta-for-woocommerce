<?php
/**
 * Ajax callbacks
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

/**
 * Class AJAX
 *
 * @package Nova_Poshta\Core
 */
class AJAX {

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
	 * AJAX constructor.
	 *
	 * @param API           $api           API for Nova Poshta.
	 * @param Shipping_Cost $shipping_cost Calculate a shipping cost.
	 */
	public function __construct( API $api, Shipping_Cost $shipping_cost ) {
		$this->api           = $api;
		$this->shipping_cost = $shipping_cost;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'wp_ajax_shipping_nova_poshta_for_woocommerce_city', [ $this, 'cities' ] );
		add_action( 'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_city', [ $this, 'cities' ] );
		add_action( 'wp_ajax_shipping_nova_poshta_for_woocommerce_warehouse', [ $this, 'warehouses' ] );
		add_action( 'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_warehouse', [ $this, 'warehouses' ] );
	}

	/**
	 * List of the cities by search field
	 */
	public function cities() {
		check_ajax_referer( Main::PLUGIN_SLUG, 'nonce' );
		$search = filter_input( INPUT_POST, 'search', FILTER_SANITIZE_STRING );
		$cities = $this->api->cities( $search, 10 );
		foreach ( $cities as $key => $city ) {
			$cities[ $key ] = [
				'id'   => $key,
				'text' => $city,
			];
		}
		wp_send_json( array_values( $cities ) );
	}

	/**
	 * List of warehouses by city
	 */
	public function warehouses() {
		check_ajax_referer( Main::PLUGIN_SLUG, 'nonce' );
		$city       = filter_input( INPUT_POST, 'city', FILTER_SANITIZE_STRING );
		$warehouses = $this->api->warehouses( $city );
		foreach ( $warehouses as $key => $warehouse ) {
			$warehouses[ $key ] = [
				'id'   => $key,
				'text' => $warehouse,
			];
		}
		wp_send_json( array_values( $warehouses ) );
	}

	/**
	 * Shipping cost
	 */
	public function shipping_cost() {
		check_ajax_referer( Main::PLUGIN_SLUG, 'nonce' );
		$city   = filter_input( INPUT_POST, 'city', FILTER_SANITIZE_STRING );
		$result = 0;
		if ( $city ) {
			$result = $this->shipping_cost->calculate( $city );
		}
		wp_send_json( $result );
	}

}
