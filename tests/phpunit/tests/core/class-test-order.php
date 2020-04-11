<?php
/**
 * Order tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Order
 *
 * @package Nova_Poshta\Core
 */
class Test_Order extends Test_Case {

	/**
	 * End test
	 */
	public function tearDown(): void {
		parent::tearDown();
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST );
	}

	/**
	 * Test updating nonce after registration new user
	 */
	public function test_update_nonce_for_new_users() {
		$nonce     = 'some-nonce';
		$new_nonce = 'some-new-nonce';
		FunctionMocker::replace( 'filter_input', $nonce );
		WP_Mock::userFunction( 'wp_create_nonce' )->
		withArgs( [ Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( $new_nonce );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );

		$order = new Order( $api );

		$order->update_nonce_for_new_users();
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$this->assertSame( $new_nonce, $_POST['woo_nova_poshta_nonce'] );
		//phpcs:enable WordPress.Security.NonceVerification.Missing
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	/**
	 * Test don't save with empty nonce
	 */
	public function test_dont_save_with_empty_nonce() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );

		$order = new Order( $api );

		$order->save( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save with bad nonce
	 */
	public function test_dont_save_with_bad_nonce() {
		$nonce                          = 'nonce';
		$_POST['woo_nova_poshta_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( false );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );

		$order = new Order( $api );

		$order->save( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save for other shipping method
	 */
	public function test_dont_save_for_other_shipping_method() {
		$nonce                          = 'nonce';
		$_POST['woo_nova_poshta_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'no_woo_nova_poshta' );
		$package_key = 10;
		$package     = [];
		$wc_order    = Mockery::mock( 'WC_Order' );

		$order = new Order( $api );

		$order->save( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save with not enough dating
	 */
	public function test_dont_save_with_empty_city_or_warehouse() {
		$nonce                          = 'nonce';
		$_POST['woo_nova_poshta_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'woo_nova_poshta' );
		$package_key = 10;
		$package     = [];
		$wc_order    = Mockery::mock( 'WC_Order' );

		$order = new Order( $api );

		$order->save( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test save with fail nonce
	 */
	public function test_save() {
		global $city_id, $warehouse_id;
		$city_id                        = 'city-id';
		$warehouse_id                   = 'warehouse-id';
		$nonce                          = 'nonce';
		$_POST['woo_nova_poshta_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		FunctionMocker::replace(
			'filter_input',
			function () {
				global $city_id, $warehouse_id;
				static $i = 0;

				$answers = [ $city_id, $warehouse_id ];

				return $answers[ $i ++ ];
			}
		);
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'woo_nova_poshta' );
		$item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'city_id', $city_id, true ] )
			->once();
		$item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'warehouse_id', $warehouse_id, true ] )
			->once();
		$package_key = 10;
		$package     = [];
		$wc_order    = Mockery::mock( 'WC_Order' );

		$order = new Order( $api );

		$order->save( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Label cases
	 *
	 * @return array
	 */
	public function provider_labels() {
		return [
			[
				'key',
				'key',
			],
			[
				'city_id',
				'City',
			],
			[
				'warehouse_id',
				'Warehouse',
			],
			[
				'internet_document',
				'Invoice',
			],
		];
	}

	/**
	 * Test correct names labels
	 *
	 * @dataProvider provider_labels
	 *
	 * @param string $key    Key name.
	 * @param string $result Key result.
	 */
	public function test_labels( string $key, string $result ) {
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->between( 1, 3 )
			->andReturn( $key );
		$order = new Order( $api );

		$this->assertSame( $result, $order->labels( $key, $wc_meta_data ) );
	}

	/**
	 * Test correct city value
	 */
	public function test_city_value() {
		$key       = 'city_id';
		$value     = 'city-id';
		$city_name = 'City Name';
		$api       = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->withArgs( [ $value ] )
			->once()
			->andReturn( $city_name );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->once()
			->andReturn( $key );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'value' ] )
			->twice()
			->andReturn( $value );
		$order = new Order( $api );

		$this->assertSame( $city_name, $order->values( $value, $wc_meta_data ) );
	}

	/**
	 * Test correct warehouse value
	 */
	public function test_warehouse_value() {
		$key            = 'warehouse_id';
		$value          = 'warehouse-id';
		$warehouse_name = 'Warehouse Name';
		$api            = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'warehouse' )
			->withArgs( [ $value ] )
			->once()
			->andReturn( $warehouse_name );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->twice()
			->andReturn( $key );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'value' ] )
			->twice()
			->andReturn( $value );
		$order = new Order( $api );

		$this->assertSame( $warehouse_name, $order->values( $value, $wc_meta_data ) );
	}

	/**
	 * Test other values
	 */
	public function test_values() {
		$key          = 'other_key';
		$value        = 'other_value';
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->twice()
			->andReturn( $key );
		$order = new Order( $api );

		$this->assertSame( $value, $order->values( $value, $wc_meta_data ) );
	}

}
