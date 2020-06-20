<?php
/**
 * API tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use DateTime;
use DateTimeZone;
use Exception;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_API
 *
 * @package Nova_Poshta\Core
 */
class Test_API extends Test_Case {

	/**
	 * Test hooks
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_hooks() {
		$db            = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$settings      = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$api           = new API( $db, $factory_cache, $settings );
		expect( 'plugin_dir_path' )
			->withAnyArgs()
			->once();
		expect( 'plugin_basename' )
			->withAnyArgs()
			->once()
			->andReturn( 'path/to/main-file' );
		expect( 'register_activation_hook' )
			->with(
				'path/to/main-file.php',
				[
					$api,
					'activate',
				]
			)
			->once();
		$api->hooks();
	}

	/**
	 * Test activate API actions
	 */
	public function test_activate_with_api_key() {
		$db            = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$settings      = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( 'api-key' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API[cities]', [ $db, $factory_cache, $settings ] );
		$api
			->shouldReceive( 'cities' )
			->once();
		$api->activate();
	}

	/**
	 * Test search cities
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_cities() {
		$api_key        = 'api-key';
		$search         = 'search';
		$limit          = 11;
		$cities         = [ 'City 1', 'City 2' ];
		$day_in_seconds = 1234;
		$request        = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		$constant       = FunctionMocker::replace( 'constant', $day_in_seconds );
		$db             = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'update_cities' )
			->with( $request['data'] )
			->once();
		$db
			->shouldReceive( 'cities' )
			->with( $search, $limit )
			->once()
			->andReturn( $cities );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Address',
					'calledMethod'     => 'getCities',
					'methodProperties' => new stdClass(),
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->andReturn( json_encode( $request ) );
		expect( 'is_wp_error' )
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->with( json_encode( $request ) )
			->once()
			->andReturn( false );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->andReturn( 'json' );
		$transient_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'get' )
			->with( 'cities' )
			->andReturn( false );
		$transient_cache
			->shouldReceive( 'set' )
			->with( 'cities', 1, $day_in_seconds );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'transient' )
			->once()
			->andReturn( $transient_cache );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $cities, $api->cities( $search, $limit ) );

		$constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
	}

	/**
	 * Test city by city_id
	 */
	public function test_city() {
		$city_id   = 'city_id';
		$city_name = 'City Name';
		$db        = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'city' )
			->with( $city_id )
			->once()
			->andReturn( $city_name );
		$settings      = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $city_name, $api->city( $city_id ) );
	}

	/**
	 * Test area by city_id
	 */
	public function test_area() {
		$city_id   = 'city_id';
		$area_name = 'City Name';
		$db        = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->with( $city_id )
			->once()
			->andReturn( $area_name );
		$settings      = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $area_name, $api->area( $city_id ) );
	}

	/**
	 * Test warehouse by warehouse_id
	 */
	public function test_warehouse() {
		$warehouse_id   = 'warehouse_id';
		$warehouse_name = 'Warehouse Name';
		$db             = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'warehouse' )
			->with( $warehouse_id )
			->once()
			->andReturn( $warehouse_name );
		$settings      = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $warehouse_name, $api->warehouse( $warehouse_id ) );
	}

	/**
	 * Test warehouse by city_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouses() {
		$api_key        = 'api-key';
		$city_id        = 'city_id';
		$warehouses     = [ 'Warehouse 1', 'Warehouse 2' ];
		$day_in_seconds = 1234;
		$request        = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		$constant       = FunctionMocker::replace( 'constant', $day_in_seconds );
		$db             = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'update_warehouses' )
			->with( $request['data'] )
			->once();
		$db
			->shouldReceive( 'warehouses' )
			->once()
			->andReturn( $warehouses );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'AddressGeneral',
					'calledMethod'     => 'getWarehouses',
					'methodProperties' => (object) [
						'CityRef' => $city_id,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->andReturn( json_encode( $request ) );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->andReturn( 'json' );
		expect( 'is_wp_error' )
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->with( json_encode( $request ) )
			->once()
			->andReturn( false );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( 'warehouse-' . $city_id )
			->once()
			->andReturn( false );
		$object_cache
			->shouldReceive( 'set' )
			->with( 'warehouse-' . $city_id, 1, $day_in_seconds )
			->once();
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'object' )
			->once()
			->andReturn( $object_cache );
		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $warehouses, $api->warehouses( $city_id ) );

		$constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
	}

	/**
	 * Shipping cost
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost() {
		$city_id       = 'city-id';
		$admin_city_id = 'admin-city-id';
		$api_key       = 'api-key';
		$weight        = 5.37;
		$volume        = 0.157;
		$cost          = 48;
		$date          = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );
		$response      = [
			'success' => true,
			'data'    => [
				[
					'CostWarehouseWarehouse' => $cost,
				],
			],
		];
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'InternetDocument',
					'calledMethod'     => 'getDocumentPrice',
					'methodProperties' => (object) [
						'CitySender'    => $admin_city_id,
						'CityRecipient' => $city_id,
						'CargoType'     => 'Parcel',
						'DateTime'      => $date->format( 'd.m.Y' ),
						'VolumeGeneral' => $volume,
						'Weight'        => $weight,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->andReturn( json_encode( $response ) );
		$db           = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( 'shipping-from-' . $admin_city_id . '-to-' . $city_id . '-' . $weight . '-' . $volume )
			->once()
			->andReturn( false );
		$object_cache
			->shouldReceive( 'set' )
			->with( 'shipping-from-' . $admin_city_id . '-to-' . $city_id . '-' . $weight . '-' . $volume, $cost, 300 )
			->once();
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'object' )
			->once()
			->andReturn( $object_cache );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $admin_city_id );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame( $cost, $api->shipping_cost( $city_id, $weight, $volume ) );
	}

	/**
	 * Test create internet document
	 */
	public function test_internet_document_without_admin_data() {
		$first_name   = 'First Name';
		$last_name    = 'Last Name';
		$phone        = '123456789';
		$city_id      = 'city_id';
		$warehouse_id = 'warehouse_id';
		$price        = '100.5';
		$count        = '10';
		$db           = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings     = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'phone' )
			->once();
		$settings
			->shouldReceive( 'city_id' )
			->once();
		$settings
			->shouldReceive( 'warehouse_id' )
			->once();
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad sender
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_internet_document_with_bad_sender() {
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city_id';
		$warehouse_id       = 'warehouse_id';
		$price              = '100.5';
		$count              = '10';
		$admin_phone        = '987654321';
		$admin_city_id      = 'admin_city_id';
		$admin_warehouse_id = 'admin_warehouse_id';
		$db                 = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings           = Mockery::mock( 'Nova_Poshta\Core\Settings' );
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
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( false );
		when( '__' )->returnArg();
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad sender
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_internet_document_with_bad_sender_2() {
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city_id';
		$warehouse_id       = 'warehouse_id';
		$price              = '100.5';
		$count              = '10';
		$admin_phone        = '987654321';
		$admin_city_id      = 'admin_city_id';
		$admin_warehouse_id = 'admin_warehouse_id';
		$api_key            = 'api-key';
		$sender             = 'sender';
		$db                 = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings           = Mockery::mock( 'Nova_Poshta\Core\Settings' );
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
		$settings
			->shouldReceive( 'api_key' )
			->times( 4 )
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterparties',
					'methodProperties' => (object) [
						'City'                 => $admin_city_id,
						'CounterpartyProperty' => 'Sender',
						'Page'                 => 1,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterpartyContactPersons',
					'methodProperties' => (object) [
						'Ref' => $sender,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->andReturn( json_encode( [ 'success' => false ] ) );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->twice()
			->andReturn( 'json' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad recipient
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_internet_document_with_bad_recipient() {
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city-id';
		$area_id            = 'area-id';
		$warehouse_id       = 'warehouse_id';
		$price              = '100.5';
		$count              = '10';
		$admin_phone        = '987654321';
		$admin_city_id      = 'admin_city_id';
		$admin_warehouse_id = 'admin_warehouse_id';
		$api_key            = 'api-key';
		$sender             = 'sender';
		$contact_sender     = 'contact-sender';
		$db                 = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->once()
			->andReturn( $area_id );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
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
		$settings
			->shouldReceive( 'api_key' )
			->times( 6 )
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterparties',
					'methodProperties' => (object) [
						'City'                 => $admin_city_id,
						'CounterpartyProperty' => 'Sender',
						'Page'                 => 1,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterpartyContactPersons',
					'methodProperties' => (object) [
						'Ref' => $sender,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'save',
					'methodProperties' => (object) [
						'CounterpartyProperty' => 'Recipient',
						'CounterpartyType'     => 'PrivatePerson',
						'FirstName'            => $first_name,
						'LastName'             => $last_name,
						'Phone'                => '380' . $phone,
						'RecipientsPhone'      => '380' . $phone,
						'Region'               => $area_id,
						'City'                 => $city_id,
						'CityRecipient'        => $city_id,
						'RecipientAddress'     => $warehouse_id,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			->andReturn( json_encode( [ 'success' => false ] ) );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->times( 3 )
			->andReturn( 'json' );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_internet_document() {
		$api_key            = 'api-key';
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city-id';
		$warehouse_id       = 'warehouse-id';
		$area_id            = 'area-id';
		$price              = '100.5';
		$count              = '10';
		$weight             = 11;
		$volume             = 22;
		$admin_phone        = '987654321';
		$description        = 'Product';
		$admin_city_id      = 'admin-city-id';
		$admin_warehouse_id = 'admin-warehouse-id';
		$sender             = 'sender';
		$contact_sender     = 'contact-sender';
		$recipient          = 'recipient';
		$contact_recipient  = 'contact-recipient';
		$internet_document  = '1234567890123456';
		$db                 = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->once()
			->andReturn( $area_id );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'phone' )
			->once()
			->andReturn( $admin_phone );
		$settings
			->shouldReceive( 'description' )
			->once()
			->andReturn( $description );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $admin_city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $admin_warehouse_id );
		$settings
			->shouldReceive( 'api_key' )
			->times( 8 )
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterparties',
					'methodProperties' => (object) [
						'City'                 => $admin_city_id,
						'CounterpartyProperty' => 'Sender',
						'Page'                 => 1,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterpartyContactPersons',
					'methodProperties' => (object) [
						'Ref' => $sender,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->once()
			->with( 'response' )
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'save',
					'methodProperties' => (object) [
						'CounterpartyProperty' => 'Recipient',
						'CounterpartyType'     => 'PrivatePerson',
						'FirstName'            => $first_name,
						'LastName'             => $last_name,
						'Phone'                => '380' . $phone,
						'RecipientsPhone'      => '380' . $phone,
						'Region'               => $area_id,
						'City'                 => $city_id,
						'CityRecipient'        => $city_id,
						'RecipientAddress'     => $warehouse_id,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref'           => $recipient,
								'ContactPerson' => [
									'data' => [
										[
											'Ref' => $contact_recipient,
										],
									],
								],
							],
						],
					]
				)
			);
		$date = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_document_description' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'InternetDocument',
					'calledMethod'     => 'save',
					'methodProperties' => (object) [
						'ContactSender'    => $contact_sender,
						'CitySender'       => $admin_city_id,
						'SenderAddress'    => $admin_warehouse_id,
						'SendersPhone'     => '380' . $admin_phone,
						'Sender'           => $sender,
						'FirstName'        => $first_name,
						'LastName'         => $last_name,
						'Phone'            => '380' . $phone,
						'RecipientsPhone'  => '380' . $phone,
						'Region'           => $area_id,
						'City'             => $city_id,
						'CityRecipient'    => $city_id,
						'RecipientAddress' => $warehouse_id,
						'Recipient'        => $recipient,
						'ContactRecipient' => $contact_recipient,
						'ServiceType'      => 'WarehouseWarehouse',
						'PaymentMethod'    => 'Cash',
						'PayerType'        => 'Recipient',
						'Cost'             => $price,
						'SeatsAmount'      => 1,
						'OptionsSeat'      => [
							[
								'volumetricVolume' => 1,
								'volumetricWidth'  => $count * 26,
								'volumetricLength' => $count * 14.5,
								'volumetricHeight' => $count * 10,
								'weight'           => ( $count * .5 ) - .01,
							],
						],
						'Description'      => $description,
						'Weight'           => ( $count * .5 ) - .01,
						'CargoType'        => 'Parcel',
						'DateTime'         => $date->format( 'd.m.Y' ),
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'IntDocNumber' => $internet_document,
							],
						],
					]
				)
			);
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->times( 4 )
			->andReturn( 'json' );
		expect( 'is_wp_error' )
			->withAnyArgs()
			->times( 4 )
			->andReturn( false );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame(
			$internet_document,
			$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $weight, $volume )
		);
	}

	/**
	 * Test create internet document with redelivery
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_internet_document_with_redelivery() {
		$api_key            = 'api-key';
		$first_name         = 'First Name';
		$last_name          = 'Last Name';
		$phone              = '123456789';
		$city_id            = 'city-id';
		$warehouse_id       = 'warehouse-id';
		$area_id            = 'area-id';
		$price              = '100.5';
		$count              = '10';
		$admin_phone        = '987654321';
		$description        = 'Product';
		$weight             = 11;
		$volume             = 22;
		$redelivery         = 100;
		$admin_city_id      = 'admin-city-id';
		$admin_warehouse_id = 'admin-warehouse-id';
		$sender             = 'sender';
		$contact_sender     = 'contact-sender';
		$recipient          = 'recipient';
		$contact_recipient  = 'contact-recipient';
		$internet_document  = '1234567890123456';
		$db                 = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'area' )
			->once()
			->andReturn( $area_id );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'phone' )
			->once()
			->andReturn( $admin_phone );
		$settings
			->shouldReceive( 'description' )
			->once()
			->andReturn( $description );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $admin_city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $admin_warehouse_id );
		$settings
			->shouldReceive( 'api_key' )
			->times( 8 )
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterparties',
					'methodProperties' => (object) [
						'City'                 => $admin_city_id,
						'CounterpartyProperty' => 'Sender',
						'Page'                 => 1,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'getCounterpartyContactPersons',
					'methodProperties' => (object) [
						'Ref' => $sender,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->once()
			->with( 'response' )
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				)
			);
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Counterparty',
					'calledMethod'     => 'save',
					'methodProperties' => (object) [
						'CounterpartyProperty' => 'Recipient',
						'CounterpartyType'     => 'PrivatePerson',
						'FirstName'            => $first_name,
						'LastName'             => $last_name,
						'Phone'                => '380' . $phone,
						'RecipientsPhone'      => '380' . $phone,
						'Region'               => $area_id,
						'City'                 => $city_id,
						'CityRecipient'        => $city_id,
						'RecipientAddress'     => $warehouse_id,
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref'           => $recipient,
								'ContactPerson' => [
									'data' => [
										[
											'Ref' => $contact_recipient,
										],
									],
								],
							],
						],
					]
				)
			);
		$date = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_document_description' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'InternetDocument',
					'calledMethod'     => 'save',
					'methodProperties' => (object) [
						'ContactSender'    => $contact_sender,
						'CitySender'       => $admin_city_id,
						'SenderAddress'    => $admin_warehouse_id,
						'SendersPhone'     => '380' . $admin_phone,
						'Sender'           => $sender,
						'FirstName'        => $first_name,
						'LastName'         => $last_name,
						'Phone'            => '380' . $phone,
						'RecipientsPhone'  => '380' . $phone,
						'Region'           => $area_id,
						'City'             => $city_id,
						'CityRecipient'    => $city_id,
						'RecipientAddress' => $warehouse_id,
						'Recipient'        => $recipient,
						'ContactRecipient' => $contact_recipient,
						'ServiceType'      => 'WarehouseWarehouse',
						'PaymentMethod'    => 'Cash',
						'PayerType'        => 'Recipient',
						'Cost'             => $price,
						'SeatsAmount'      => 1,
						'OptionsSeat'      => [
							[
								'volumetricVolume' => 1,
								'volumetricWidth'  => $count * 26,
								'volumetricLength' => $count * 14.5,
								'volumetricHeight' => $count * 10,
								'weight'           => ( $count * .5 ) - .01,
							],
						],
						'Description'      => $description,
						'Weight'           => ( $count * .5 ) - .01,
						'CargoType'        => 'Parcel',
						'DateTime'         => $date->format( 'd.m.Y' ),
					],
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( 'response' );
		expect( 'wp_remote_retrieve_body' )
			->with( 'response' )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'IntDocNumber' => $internet_document,
							],
						],
					]
				)
			);
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->times( 4 )
			->andReturn( 'json' );
		expect( 'is_wp_error' )
			->withAnyArgs()
			->times( 4 )
			->andReturn( false );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertSame(
			$internet_document,
			$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $weight, $volume, $redelivery )
		);
	}

	/**
	 * Test validate bad request API key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_validation_bad_request() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Address',
					'calledMethod'     => 'getCities',
					'apiKey'           => $api_key,
					'methodProperties' => (object) [
						'FindByString' => 'Киев',
					],
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
				]
			)
			->once()
			->andReturn( false );
		expect( 'is_wp_error' )
			->withAnyArgs()
			->once()
			->andReturn( true );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertFalse( $api->validate( $api_key ) );
	}

	/**
	 * Test fail validation API key.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_fail_validation() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Address',
					'calledMethod'     => 'getCities',
					'apiKey'           => $api_key,
					'methodProperties' => (object) [
						'FindByString' => 'Киев',
					],
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
				]
			)
			->once()
			->andReturn(
				[
					//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
					'body' => json_encode(
						[
							'success' => false,
						]
					),
				]
			);
		expect( 'is_wp_error' )
			->withAnyArgs()
			->once()
			->andReturn( false );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertFalse( $api->validate( $api_key ) );
	}

	/**
	 * Test validation API key.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_validation() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Address',
					'calledMethod'     => 'getCities',
					'apiKey'           => $api_key,
					'methodProperties' => (object) [
						'FindByString' => 'Киев',
					],
				]
			)
			->once()
			->andReturn( 'json' );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
				]
			)
			->once()
			->andReturn(
				[
					//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
					'body' => json_encode(
						[
							'success' => true,
						]
					),
				]
			);
		expect( 'is_wp_error' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );

		$api = new API( $db, $factory_cache, $settings );

		$this->assertTrue( $api->validate( $api_key ) );
	}

	/**
	 * Test errors
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_errors_request() {
		$api_key        = 'api-key';
		$search         = 'search';
		$limit          = 11;
		$cities         = [ 'City 1', 'City 2' ];
		$day_in_seconds = 1234;
		$request        = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		$db             = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'cities' )
			->with( $search, $limit )
			->once()
			->andReturn( $cities );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		expect( 'wp_json_encode' )
			->with(
				[
					'modelName'        => 'Address',
					'calledMethod'     => 'getCities',
					'methodProperties' => new stdClass(),
					'apiKey'           => $api_key,
				]
			)
			->once()
			->andReturn( 'json' );
		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error
			->shouldReceive( 'get_error_messages' )
			->once()
			->andReturn( [ 'error1', 'error2' ] );
		expect( 'wp_remote_post' )
			->with(
				API::ENDPOINT,
				[
					'headers'     => [ 'Content-Type' => 'application/json' ],
					'body'        => 'json',
					'data_format' => 'body',
					'timeout'     => 5,
				]
			)
			->once()
			->andReturn( $wp_error );
		expect( 'wp_remote_retrieve_body' )
			->with( $wp_error )
			->once()
			->andReturn(
			//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				json_encode(
					[
						'success' => false,
						'errors'  => [ 'error3', 'error4' ],
					]
				)
			);
		expect( 'is_wp_error' )
			->withAnyArgs()
			->once()
			->andReturn( $wp_error );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_request_body' )
			->with( 'json' )
			->once()
			->andReturn( 'json' );
		$transient_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'get' )
			->with( 'cities' )
			->andReturn( false );
		$transient_cache
			->shouldReceive( 'set' )
			->with( 'cities', 1, $day_in_seconds );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'transient' )
			->once()
			->andReturn( $transient_cache );

		$api = new API( $db, $factory_cache, $settings );
		$api->cities( $search, $limit );
		$this->assertSame(
			[
				'error1',
				'error2',
				'error3',
				'error4',
			],
			$api->errors()
		);
	}

}
