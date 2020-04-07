<?php
/**
 * Ajax tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;

/**
 * Class Test_Notice
 *
 * @package Nova_Poshta\Admin
 */
class Test_Ajax extends Test_Case {

	/**
	 * Test search cities
	 */
	public function test_cities() {
		$city_id   = 'city_id';
		$city_name = 'City Name';
		\WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		\WP_Mock::userFunction( 'wp_send_json' )->
		withArgs(
			[
				[
					[
						'id'   => $city_id,
						'text' => $city_name,
					],
				],
			]
		)->
		once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_name );
		$api          = \Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->withArgs( [ $city_name, 10 ] )
			->andReturn( [ $city_id => $city_name ] );
		$ajax = new AJAX( $api );

		$ajax->cities();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'search', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Test search city warehouses
	 */
	public function test_warehouses() {
		$city_id        = 'city_id';
		$warehouse_id   = 'warehouse_id';
		$warehouse_name = 'City Name';
		\WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		\WP_Mock::userFunction( 'wp_send_json' )->
		withArgs(
			[
				[
					[
						'id'   => $warehouse_id,
						'text' => $warehouse_name,
					],
				],
			]
		)->
		once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$api          = \Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->withArgs( [ $city_id ] )
			->andReturn( [ $warehouse_id => $warehouse_name ] );
		$ajax = new AJAX( $api );

		$ajax->warehouses();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

}
