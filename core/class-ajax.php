<?php
/**
 * Ajax callbacks
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
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
	 * AJAX constructor.
	 *
	 * @param API $api API for Nova Poshta.
	 */
	public function __construct( API $api ) {
		$this->api = $api;
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

}
