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
	 * Test don't save other shipping method
	 */
	public function test_dont_save() {
		// todo: why do we need this?
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		times( 0 );
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
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		times( 0 );
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
		$city_id            = 'city-id';
		$warehouse_id       = 'warehouse-id';
		$billing_first_name = 'billing_first_name';
		$billing_last_name  = 'billing_last_name';
		$billing_phone      = 'billing_phone';
		$total              = 300;
		$internet_document  = '1234 5678 9012 3456';
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		times( 0 );
		$filter_input = FunctionMocker::replace(
			'filter_input',
			function () {
				global $city_id, $warehouse_id;
				static $i = 0;

				$answers = [ $city_id, $warehouse_id ];

				return $answers[ $i ++ ];
			}
		);
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->withArgs(
				[
					$billing_first_name,
					$billing_last_name,
					$billing_phone,
					$city_id,
					$warehouse_id,
					$total,
					15,
				]
			)
			->once()
			->andReturn( $internet_document );

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
		$item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once()
			->andReturn( $city_id );
		$item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once()
			->andReturn( $warehouse_id );
		$item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'internet_document', $internet_document, true ] )
			->once();
		$package_key = 10;
		$package     = [];
		$wc_order    = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_billing_first_name' )
			->once()
			->andReturn( $billing_first_name );
		$wc_order
			->shouldReceive( 'get_billing_last_name' )
			->once()
			->andReturn( $billing_last_name );
		$wc_order
			->shouldReceive( 'get_billing_phone' )
			->once()
			->andReturn( $billing_phone );
		$wc_order
			->shouldReceive( 'get_total' )
			->once()
			->andReturn( $total );
		$wc_order_item_1 = Mockery::mock( 'WC_Item' );
		$wc_order_item_1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 5 );
		$wc_order_item_2 = Mockery::mock( 'WC_Item' );
		$wc_order_item_2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 10 );
		$wc_order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $wc_order_item_1, $wc_order_item_2 ] );

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
