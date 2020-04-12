<?php
/**
 * API tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_API
 *
 * @package Nova_Poshta\Core
 */
class Test_API extends Test_Case {

	/**
	 * Test search cities
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_cities() {
		// todo: we don't need overload and slow tests here.
		// todo: use Mockery::mock('API')->makePartial(), then call test method.
		// todo: to provide $this->np? make it protected and set_protected_property(), as in Cyr_To_Lat_TestCase.


		// todo: replace DAY_IN_SECONDS in the code by the `constant( 'DAY_IN_SECONDS' )`. Mock constant then.
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}

		$search  = 'search';
		$limit   = 11;
		$cities  = [ 'City 1', 'City 2' ];
		$request = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		WP_Mock::userFunction( 'get_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-cities' ] )->
		once()->
		andReturn( false );
		WP_Mock::userFunction( 'set_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-cities', 1, DAY_IN_SECONDS ] )->
		once();
		$np = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'getCities' )
			->withArgs( [ 0 ] )
			->once()
			->andReturn( $request );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'update_cities' )
			->withArgs( [ $request['data'] ] )
			->once();
		$db
			->shouldReceive( 'cities' )
			->withArgs( [ $search, $limit ] )
			->once()
			->andReturn( $cities );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertSame( $cities, $api->cities( $search, $limit ) );
	}

	/**
	 * Test city by city_id
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_city() {
		$city_id   = 'city_id';
		$city_name = 'City Name';
		Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'city' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $city_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertSame( $city_name, $api->city( $city_id ) );
	}

	/**
	 * Test area by city_id
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_area() {
		$city_id   = 'city_id';
		$area_name = 'City Name';
		Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $area_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertSame( $area_name, $api->area( $city_id ) );
	}

	/**
	 * Test warehouse by warehouse_id
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_warehouse() {
		$warehouse_id   = 'warehouse_id';
		$warehouse_name = 'Warehouse Name';
		Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'warehouse' )
			->withArgs( [ $warehouse_id ] )
			->once()
			->andReturn( $warehouse_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertSame( $warehouse_name, $api->warehouse( $warehouse_id ) );
	}

	/**
	 * Test warehouse by city_id
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_warehouses() {
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}
		$city_id    = 'city_id';
		$warehouses = [ 'Warehouse 1', 'Warehouse 2' ];
		$request    = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		WP_Mock::userFunction( 'get_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-warehouse-' . $city_id ] )->
		once()->
		andReturn( false );
		WP_Mock::userFunction( 'set_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-warehouse-' . $city_id, 1, DAY_IN_SECONDS ] )->
		once();
		$np = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'getWarehouses' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $request );

		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'update_warehouses' )
			->withArgs( [ $request['data'] ] )
			->once()
			->andReturn( true );
		$db
			->shouldReceive( 'warehouses' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $warehouses );

		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertSame( $warehouses, $api->warehouses( $city_id ) );
	}

	/**
	 * Test create internet document
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_internet_document_without_key() {
		$first_name   = 'First Name';
		$last_name    = 'Last Name';
		$phone        = '123456789';
		$city_id      = 'city_id';
		$warehouse_id = 'warehouse_id';
		$price        = '100.5';
		$count        = '10';
		Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( false );

		$api = new API( $db, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_internet_document_without_admin_data() {
		$first_name   = 'First Name';
		$last_name    = 'Last Name';
		$phone        = '123456789';
		$city_id      = 'city_id';
		$warehouse_id = 'warehouse_id';
		$price        = '100.5';
		$count        = '10';
		$api_key      = 'api-key';
		$np           = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'setKey' )
			->withArgs( [ $api_key ] )
			->once();
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		$settings
			->shouldReceive( 'phone' )
			->once();
		$settings
			->shouldReceive( 'city_id' )
			->once();
		$settings
			->shouldReceive( 'warehouse_id' )
			->once();

		$api = new API( $db, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_internet_document() {
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city_id';
		$area               = 'area_name';
		$warehouse_id       = 'warehouse_id';
		$price              = 100.5;
		$count              = 10;
		$api_key            = 'api-key';
		$admin_phone        = '987654321';
		$admin_city_id      = 'admin_city_id';
		$admin_warehouse_id = 'admin_warehouse_id';
		$internet_document  = [
			'success' => true,
			'data'    => [
				[
					'IntDocNumber' => '1234 5678 9012 3456',
				],
			],
		];
		$np                 = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'setKey' )
			->withArgs( [ $api_key ] )
			->once();
		$np
			->shouldReceive( 'newInternetDocument' )
			->withArgs(
				[
					[
						'ContactSender' => $admin_phone,
						'CitySender'    => $admin_city_id,
						'SenderAddress' => $admin_warehouse_id,
					],
					[
						'FirstName'        => $first_name,
						'LastName'         => $last_name,
						'Phone'            => $phone,
						'Region'           => $area,
						'City'             => $city_id,
						'CityRecipient'    => $city_id,
						'RecipientAddress' => $warehouse_id,
					],
					[
						'ServiceType'   => 'WarehouseWarehouse',
						'PaymentMethod' => 'Cash',
						'PayerType'     => 'Recipient',
						'Cost'          => $price,
						'SeatsAmount'   => '1',
						'Description'   => 'Взуття',
						'Weight'        => ( $count * .5 ) - .01,
					],
				]
			)
			->andReturn( $internet_document );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $area );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		$settings
			->shouldReceive( 'phone' )
			->once()
			->andReturn( $admin_phone );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $admin_city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $admin_warehouse_id );

		$api = new API( $db, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with redelivery
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_internet_document_with_redelivery() {
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city_id';
		$area               = 'area_name';
		$warehouse_id       = 'warehouse_id';
		$price              = 100.5;
		$count              = 10;
		$redelivery         = 300.7;
		$api_key            = 'api-key';
		$admin_phone        = '987654321';
		$admin_city_id      = 'admin_city_id';
		$admin_warehouse_id = 'admin_warehouse_id';
		$internet_document  = [
			'success' => true,
			'data'    => [
				[
					'IntDocNumber' => '1234 5678 9012 3456',
				],
			],
		];
		$np                 = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'setKey' )
			->withArgs( [ $api_key ] )
			->once();
		$np
			->shouldReceive( 'newInternetDocument' )
			->withArgs(
				[
					[
						'ContactSender' => $admin_phone,
						'CitySender'    => $admin_city_id,
						'SenderAddress' => $admin_warehouse_id,
					],
					[
						'FirstName'        => $first_name,
						'LastName'         => $last_name,
						'Phone'            => $phone,
						'Region'           => $area,
						'City'             => $city_id,
						'CityRecipient'    => $city_id,
						'RecipientAddress' => $warehouse_id,
					],
					[
						'ServiceType'          => 'WarehouseWarehouse',
						'PaymentMethod'        => 'Cash',
						'PayerType'            => 'Recipient',
						'Cost'                 => $price,
						'SeatsAmount'          => '1',
						'Description'          => 'Взуття',
						'Weight'               => ( $count * .5 ) - .01,
						'BackwardDeliveryData' => [
							[
								'PayerType'        => 'Recipient',
								'CargoType'        => 'Money',
								'RedeliveryString' => $redelivery,
							],
						],
					],
				]
			)
			->andReturn( $internet_document );
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $area );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		$settings
			->shouldReceive( 'phone' )
			->once()
			->andReturn( $admin_phone );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $admin_city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $admin_warehouse_id );

		$api = new API( $db, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count, $redelivery );
	}

	/**
	 * Test fail validate API key
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_fail_validation() {
		$api_key = 'api-key';
		$np      = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'setKey' )
			->withArgs( [ $api_key ] )
			->once();
		$np
			->shouldReceive( 'getCounterparties' )
			->withArgs( [ 'Sender', 1 ] )
			->once();
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertFalse( $api->validate( $api_key ) );
	}

	/**
	 * Test success validate API key
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_success_validation() {
		$api_key = 'api-key';
		$np      = Mockery::mock( 'overload:LisDev\Delivery\NovaPoshtaApi2' );
		$np
			->shouldReceive( 'setKey' )
			->withArgs( [ $api_key ] )
			->once();
		$np
			->shouldReceive( 'getCounterparties' )
			->withArgs( [ 'Sender', 1 ] )
			->once()
			->andReturn( [ 'success' => true ] );
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = new API( $db, $settings );

		$this->assertTrue( $api->validate( $api_key ) );
	}

}
