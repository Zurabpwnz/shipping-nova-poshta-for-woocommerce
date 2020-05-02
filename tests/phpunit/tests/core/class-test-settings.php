<?php
/**
 * Settings tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

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
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $api_key, $settings->api_key() );
	}

	/**
	 * Test get empty api key
	 */
	public function test_empty_api_key() {
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [] );
		WP_Mock::userFunction( 'get_admin_url' )->
		with( null, 'admin.php?page=' . Main::PLUGIN_SLUG )->
		once()->
		andReturn( 'url' );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with(
				'error',
				'For the plugin to work, you must enter the API key on the <a href="url">plugin settings page</a>'
			)->
			once();

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->api_key() );
	}

	/**
	 * Test get phone
	 */
	public function test_phone() {
		$api_key = 'api-key';
		$phone   = 'phone';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn(
			[
				'api_key' => $api_key,
				'phone'   => $phone,
			]
		);
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $phone, $settings->phone() );
	}

	/**
	 * Test get empty phone
	 */
	public function test_empty_phone() {
		$api_key = 'api-key';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->phone() );
	}


	/**
	 * Test get description
	 */
	public function test_description() {
		$api_key     = 'api-key';
		$description = 'description';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn(
			[
				'api_key'     => $api_key,
				'description' => $description,
			]
		);
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $description, $settings->description() );
	}


	/**
	 * Test get empty phone
	 */
	public function test_empty_description() {
		$api_key = 'api-key';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( 'Товар', $settings->description() );
	}

	/**
	 * Test get city_id
	 */
	public function test_city_id() {
		$api_key = 'api-key';
		$city_id = 'city-id';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn(
			[
				'api_key' => $api_key,
				'city_id' => $city_id,
			]
		);
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $city_id, $settings->city_id() );
	}

	/**
	 * Test get empty city_id
	 */
	public function test_empty_city_id() {
		$api_key = 'api-key';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->city_id() );
	}

	/**
	 * Test get warehouse_id
	 */
	public function test_warehouse_id() {
		$api_key      = 'api-key';
		$warehouse_id = 'warehouse-id';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn(
			[
				'api_key'      => $api_key,
				'warehouse_id' => $warehouse_id,
			]
		);
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $warehouse_id, $settings->warehouse_id() );
	}

	/**
	 * Test get empty warehouse_id
	 */
	public function test_empty_warehouse_id() {
		$api_key = 'api-key';
		WP_Mock::userFunction( 'get_option' )->
		withArgs( [ Main::PLUGIN_SLUG, [] ] )->
		once()->
		andReturn( [ 'api_key' => $api_key ] );
		$notice = \Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->warehouse_id() );
	}

}
