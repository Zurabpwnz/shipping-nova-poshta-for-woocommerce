<?php
/**
 * User
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\API;
use Nova_Poshta\Core\Language;
use Nova_Poshta\Core\Main;

/**
 * Class User
 *
 * @package Nova_Poshta\Admin
 */
class User {

	/**
	 * API for Nova Poshta
	 *
	 * @var API
	 */
	private $api;
	/**
	 * Plugin language
	 *
	 * @var Language
	 */
	private $language;

	/**
	 * User constructor.
	 *
	 * @param API      $api      API for Nova Poshta.
	 * @param Language $language Plugin language.
	 */
	public function __construct( API $api, Language $language ) {
		$this->api      = $api;
		$this->language = $language;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'shipping_nova_poshta_for_woocommerce_user_fields', [ $this, 'fields' ] );
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $this, 'checkout' ], 10, 4 );

		add_filter( 'shipping_nova_poshta_for_woocommerce_default_city_id', [ $this, 'city' ] );
		add_filter( 'shipping_nova_poshta_for_woocommerce_default_warehouse_id', [ $this, 'warehouse' ] );
	}

	/**
	 * Fields for nova poshta
	 * TODO: Move to other place.
	 */
	public function fields() {
		$user_id      = get_current_user_id();
		$city_id      = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_warehouse', FILTER_SANITIZE_STRING );
		if ( empty( $city_id || $warehouse_id ) ) {
			$city         = $this->api->cities(
				apply_filters(
					'shipping_nova_poshta_for_woocommerce_default_city',
					'',
					$user_id,
					$this->language->get_current_language()
				),
				1
			);
			$city_id      = apply_filters( 'shipping_nova_poshta_for_woocommerce_default_city_id', array_keys( $city )[0] ?? '', $user_id );
			$warehouses   = [ 0 => '' ];
			$warehouse_id = '';
			if ( $city_id ) {
				$warehouses   = $this->api->warehouses( $city_id );
				$warehouse_id = array_keys( $warehouses )[0] ?? '';
				$warehouse_id = apply_filters(
					'shipping_nova_poshta_for_woocommerce_default_warehouse_id',
					$warehouse_id,
					$user_id,
					$city
				);
			}
		}

		$fields = [
			'shipping_nova_poshta_for_woocommerce_city' => [
				'type'     => 'select',
				'label'    => __( 'Select delivery city', 'shipping-nova-poshta-for-woocommerce' ),
				'required' => true,
				'options'  => $city,
				'default'  => $city_id,
				'priority' => 10,
			],

			'shipping_nova_poshta_for_woocommerce_warehouse' => [
				'type'     => 'select',
				'label'    => __( 'Choose branch', 'shipping-nova-poshta-for-woocommerce' ),
				'required' => true,
				'options'  => $warehouses,
				'default'  => $warehouse_id,
				'priority' => 20,
			],
		];
		wp_nonce_field( Main::PLUGIN_SLUG . '-shipping', 'shipping_nova_poshta_for_woocommerce_nonce', false );
		foreach ( $fields as $key => $field ) {
			do_action( 'before_shipping_nova_poshta_for_woocommerce_field', $key );
			woocommerce_form_field( $key, $field );
			do_action( 'after_shipping_nova_poshta_for_woocommerce_field', $key );
		}
	}

	/**
	 * Current user city.
	 *
	 * @param string $city_id City ID.
	 *
	 * @return string
	 */
	public function city( string $city_id ): string {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_city_id = get_user_meta( $user_id, 'shipping_nova_poshta_for_woocommerce_city', true );
		}

		return ! empty( $user_city_id ) ? $user_city_id : $city_id;
	}

	/**
	 * Current user warehouse
	 *
	 * @param string $warehouse_id Warehouse ID.
	 *
	 * @return string
	 */
	public function warehouse( string $warehouse_id ): string {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_warehouse_id = get_user_meta( $user_id, 'shipping_nova_poshta_for_woocommerce_warehouse', true );
		}

		return ! empty( $user_warehouse_id ) ? $user_warehouse_id : $warehouse_id;
	}

	/**
	 * Update user_meta after each order complete
	 */
	public function checkout() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}

		$city_id      = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_warehouse', FILTER_SANITIZE_STRING );
		if ( ! $city_id || ! $warehouse_id ) {
			return;
		}
		update_user_meta( $user_id, 'shipping_nova_poshta_for_woocommerce_city', $city_id );
		update_user_meta( $user_id, 'shipping_nova_poshta_for_woocommerce_warehouse', $warehouse_id );
	}

}
