<?php
/**
 * User
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\API;
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
	 * User constructor.
	 *
	 * @param API $api API for Nova Poshta.
	 */
	public function __construct( API $api ) {
		$this->api = $api;
	}

	/**
	 * Fields for nova poshta
	 * TODO: Move to other place.
	 */
	public function fields() {
		$city_id      = filter_input( INPUT_POST, 'woo_nova_poshta_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'woo_nova_poshta_warehouse', FILTER_SANITIZE_STRING );
		if ( empty( $city_id || $warehouse_id ) ) {
			$city         = $this->api->cities(
				apply_filters( 'woo_nova_poshta_default_city', 'Киев' ),
				1
			);
			$city_id      = apply_filters( 'woo_nova_poshta_default_city_id', array_keys( $city )[0] ?? '' );
			$warehouses   = [ 0 => '' ];
			$warehouse_id = '';
			if ( $city_id ) {
				$warehouses   = $this->api->warehouses( $city_id );
				$warehouse_id = array_keys( $warehouses )[0] ?? '';
				$warehouse_id = apply_filters( 'woo_nova_poshta_default_warehouse_id', $warehouse_id, $city );
			}
		}

		$fields = [
			'woo_nova_poshta_city'      => [
				'type'     => 'select',
				'label'    => __( 'Select delivery city', 'woo-nova-poshta' ),
				'required' => true,
				'options'  => $city,
				'default'  => $city_id,
				'priority' => 10,
			],
			'woo_nova_poshta_warehouse' => [
				'type'     => 'select',
				'label'    => __( 'Choose branch', 'woo-nova-poshta' ),
				'required' => true,
				'options'  => $warehouses,
				'default'  => $warehouse_id,
				'priority' => 20,
			],
		];

		wp_nonce_field( Main::PLUGIN_SLUG . '-shipping', 'woo_nova_poshta_nonce', false );
		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field );
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
			$user_city_id = get_user_meta( $user_id, 'woo_nova_poshta_city', true );
		}

		return $user_city_id ?? $city_id;
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
			$user_warehouse_id = get_user_meta( $user_id, 'woo_nova_poshta_warehouse', true );
		}

		return $user_warehouse_id ?? $warehouse_id;
	}

	/**
	 * Update user_meta after each order complete
	 */
	public function checkout(): void {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
			return;
		}

		$city_id      = filter_input( INPUT_POST, 'woo_nova_poshta_city', FILTER_SANITIZE_STRING );
		$warehouse_id = filter_input( INPUT_POST, 'woo_nova_poshta_warehouse', FILTER_SANITIZE_STRING );
		if ( ! $city_id || ! $warehouse_id ) {
			return;
		}
		update_user_meta( $user_id, 'woo_nova_poshta_city', $city_id );
		update_user_meta( $user_id, 'woo_nova_poshta_warehouse', $warehouse_id );
	}

}
