<?php
/**
 * Settings tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;

/**
 * Class Test_Settings
 *
 * @package Nova_Poshta\Core
 */
class Test_Settings extends Test_Case {

	/**
	 * Test get api key
	 */
	public function test_api_key() {
		$api_key = 'api-key';
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );

		$settings = new Settings();

		$this->assertSame( $api_key, $settings->api_key() );
	}

	/**
	 * Test get empty api key
	 */
	public function test_empty_api_key() {
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [] );

		$settings = new Settings();

		$this->assertSame( '', $settings->api_key() );
	}

	/**
	 * Test get phone
	 */
	public function test_phone() {
		$phone = 'phone';
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'phone' => $phone ] );

		$settings = new Settings();

		$this->assertSame( $phone, $settings->phone() );
	}

	/**
	 * Test get empty phone
	 */
	public function test_empty_phone() {
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [] );

		$settings = new Settings();

		$this->assertSame( '', $settings->phone() );
	}

	/**
	 * Test get city_id
	 */
	public function test_city_id() {
		$city_id = 'city-id';
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'city_id' => $city_id ] );

		$settings = new Settings();

		$this->assertSame( $city_id, $settings->city_id() );
	}

	/**
	 * Test get empty city_id
	 */
	public function test_empty_city_id() {
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [] );

		$settings = new Settings();

		$this->assertSame( '', $settings->city_id() );
	}

	/**
	 * Test get warehouse_id
	 */
	public function test_warehouse_id() {
		$warehouse_id = 'warehouse-id';
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'warehouse_id' => $warehouse_id ] );

		$settings = new Settings();

		$this->assertSame( $warehouse_id, $settings->warehouse_id() );
	}

	/**
	 * Test get empty warehouse_id
	 */
	public function test_empty_warehouse_id() {
		\WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [] );

		$settings = new Settings();

		$this->assertSame( '', $settings->warehouse_id() );
	}

}
