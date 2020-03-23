<?php

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\API;
use Nova_Poshta\Core\Main;

class User {

	private $api;

	public function __construct( API $api ) {
		$this->api = $api;
	}

	public function fields() {
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
			$warehouse_id = apply_filters( 'woo_nova_poshta_default_warehouse', $warehouse_id, $city );
		}

		$fields = [
			'woo_nova_poshta_city'      => [
				'type'     => 'select',
				'label'    => 'Выберите город доставки',
				'required' => true,
				'options'  => $city,
				'default'  => array_keys( $city )[0] ?? '',
				'priority' => 10,
			],
			'woo_nova_poshta_warehouse' => [
				'type'     => 'select',
				'label'    => 'Выберите отделение',
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

}
