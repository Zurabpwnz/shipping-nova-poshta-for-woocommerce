<?php
/**
 * API tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use DateTime;
use DateTimeZone;
use Exception;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_API
 *
 * @package Nova_Poshta\Core
 */
class Test_API extends Test_Case {

	/**
	 * Test search cities
	 */
	public function test_cities() {
		$api_key        = 'api-key';
		$day_in_seconds = 86400;
		$search         = 'search';
		$limit          = 11;
		$cities         = [ 'City 1', 'City 2' ];
		$request        = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		$mock_constant  = FunctionMocker::replace( 'constant', $day_in_seconds );
		WP_Mock::userFunction( 'get_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-cities' ] )->
		once()->
		andReturn( false );
		WP_Mock::userFunction( 'set_transient' )->
		withArgs( [ Main::PLUGIN_SLUG . '-cities', 1, $day_in_seconds ] )->
		once();
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'update_cities' )
			->with( $request['data'] )
			->once();
		$db
			->shouldReceive( 'cities' )
			->withArgs( [ $search, $limit ] )
			->once()
			->andReturn( $cities );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( $api_key );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Address',
				'calledMethod'     => 'getCities',
				'methodProperties' => new stdClass(),
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn( [ 'body' => json_encode( $request ) ] );
		WP_Mock::userFunction( 'is_wp_error' )->
		once()->
		andReturn( false );
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );

		$api = new API( $db, $settings );

		$this->assertSame( $cities, $api->cities( $search, $limit ) );

		$mock_constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
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
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $city_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

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
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $area_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

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
			->withArgs( [ $warehouse_id ] )
			->once()
			->andReturn( $warehouse_name );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

		$this->assertSame( $warehouse_name, $api->warehouse( $warehouse_id ) );
	}

	/**
	 * Test warehouse by city_id
	 */
	public function test_warehouses() {
		$day_in_seconds = 86400;
		$api_key        = 'api-key';
		$city_id        = 'city_id';
		$warehouses     = [ 'Warehouse 1', 'Warehouse 2' ];
		$request        = [
			'success' => true,
			'data'    => [ 'some-data' ],
		];
		$mock_constant  = FunctionMocker::replace( 'constant', $day_in_seconds );
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( Main::PLUGIN_SLUG . '-warehouse-' . $city_id, Main::PLUGIN_SLUG )->
		once()->
		andReturn( false );
		WP_Mock::userFunction( 'wp_cache_set' )->
		withArgs( [ Main::PLUGIN_SLUG . '-warehouse-' . $city_id, 1, Main::PLUGIN_SLUG, $day_in_seconds ] )->
		once();
		$db = Mockery::mock( 'Nova_Poshta\Core\DB' );
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
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'AddressGeneral',
				'calledMethod'     => 'getWarehouses',
				'methodProperties' => (object) [
					'CityRef' => $city_id,
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn( [ 'body' => json_encode( $request ) ] );
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );
		WP_Mock::userFunction( 'is_wp_error' )->
		once()->
		andReturn( false );

		$api = new API( $db, $settings );

		$this->assertSame( $warehouses, $api->warehouses( $city_id ) );

		$mock_constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
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

		$api = new API( $db, $settings );

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad sender
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
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad sender
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
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Counterparty',
				'calledMethod'     => 'getCounterpartyContactPersons',
				'methodProperties' => (object) [
					'Ref' => $sender,
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode( [] ),
			]
		);
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

		$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count );
	}

	/**
	 * Test create internet document with bad recipient
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
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Counterparty',
				'calledMethod'     => 'getCounterpartyContactPersons',
				'methodProperties' => (object) [
					'Ref' => $sender,
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode( [ 'success' => false ] ),
			]
		);
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

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
		$admin_phone        = '987654321';
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
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Counterparty',
				'calledMethod'     => 'getCounterpartyContactPersons',
				'methodProperties' => (object) [
					'Ref' => $sender,
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
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
				),
			]
		);
		$date = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
					'Description'      => 'Взуття',
					'Weight'           => ( $count * .5 ) - .01,
					'CargoType'        => 'Parcel',
					'DateTime'         => $date->format( 'd.m.Y' ),
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'IntDocNumber' => $internet_document,
							],
						],
					]
				),
			]
		);
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );
		WP_Mock::userFunction( 'is_wp_error' )->
		times( 4 )->
		andReturn( false );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

		$this->assertSame(
			$internet_document,
			$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count )
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
		$price              = 100.5;
		$count              = 10;
		$redelivery         = 50;
		$admin_phone        = '987654321';
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
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Counterparty',
				'calledMethod'     => 'getCounterpartyContactPersons',
				'methodProperties' => (object) [
					'Ref' => $sender,
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'Ref' => $contact_sender,
							],
						],
					]
				),
			]
		);
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
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
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
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
				),
			]
		);
		$date = new DateTime( '', new DateTimeZone( 'Europe/Kiev' ) );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'InternetDocument',
				'calledMethod'     => 'save',
				'methodProperties' => (object) [
					'ContactSender'        => $contact_sender,
					'CitySender'           => $admin_city_id,
					'SenderAddress'        => $admin_warehouse_id,
					'SendersPhone'         => '380' . $admin_phone,
					'Sender'               => $sender,
					'FirstName'            => $first_name,
					'LastName'             => $last_name,
					'Phone'                => '380' . $phone,
					'RecipientsPhone'      => '380' . $phone,
					'Region'               => $area_id,
					'City'                 => $city_id,
					'CityRecipient'        => $city_id,
					'RecipientAddress'     => $warehouse_id,
					'Recipient'            => $recipient,
					'ContactRecipient'     => $contact_recipient,
					'ServiceType'          => 'WarehouseWarehouse',
					'PaymentMethod'        => 'Cash',
					'PayerType'            => 'Recipient',
					'Cost'                 => $price,
					'SeatsAmount'          => 1,
					'OptionsSeat'          => [
						[
							'volumetricVolume' => 1,
							'volumetricWidth'  => $count * 26,
							'volumetricLength' => $count * 14.5,
							'volumetricHeight' => $count * 10,
							'weight'           => ( $count * .5 ) - .01,
						],
					],
					'Description'          => 'Взуття',
					'Weight'               => ( $count * .5 ) - .01,
					'CargoType'            => 'Parcel',
					'DateTime'             => $date->format( 'd.m.Y' ),
					'BackwardDeliveryData' => [
						[
							'PayerType'        => 'Recipient',
							'CargoType'        => 'Money',
							'RedeliveryString' => $redelivery,
						],
					],
				],
				'apiKey'           => $api_key,
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
				'timeout'     => 30,
			]
		)->
		once()->
		//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
						'data'    => [
							[
								'IntDocNumber' => $internet_document,
							],
						],
					]
				),
			]
		);
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );
		WP_Mock::userFunction( 'is_wp_error' )->
		times( 4 )->
		andReturn( false );

		$api = Mockery::mock( 'Nova_Poshta\Core\API', [ $db, $settings ] )->makePartial();

		$this->assertSame(
			$internet_document,
			$api->internet_document( $first_name, $last_name, $phone, $city_id, $warehouse_id, $price, $count, $redelivery )
		);
	}

	/**
	 * Test validate bad request API key
	 */
	public function test_validation_bad_request() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Address',
				'calledMethod'     => 'getCities',
				'apiKey'           => $api_key,
				'methodProperties' => (object) [
					'FindByString' => 'Киев',
				],
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
			]
		)->
		once()->
		andReturn( false );
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );
		WP_Mock::userFunction( 'is_wp_error' )->
		once()->
		andReturn( true );

		$api = new API( $db, $settings );

		$this->assertFalse( $api->validate( $api_key ) );
	}

	/**
	 * Test fail validation API key.
	 */
	public function test_fail_validation() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Address',
				'calledMethod'     => 'getCities',
				'apiKey'           => $api_key,
				'methodProperties' => (object) [
					'FindByString' => 'Киев',
				],
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
			]
		)->
		once()->
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => false,
					]
				),
			]
		);
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_request_body' )->
		with( 'json' )->
		reply( 'json' );
		WP_Mock::userFunction( 'is_wp_error' )->
		once()->
		andReturn( false );

		$api = new API( $db, $settings );

		$this->assertFalse( $api->validate( $api_key ) );
	}

	/**
	 * Test validation API key.
	 */
	public function test_validation() {
		$api_key  = 'api-key';
		$db       = Mockery::mock( 'Nova_Poshta\Core\DB' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'wp_json_encode' )->
		with(
			[
				'modelName'        => 'Address',
				'calledMethod'     => 'getCities',
				'apiKey'           => $api_key,
				'methodProperties' => (object) [
					'FindByString' => 'Киев',
				],
			]
		)->
		once()->
		andReturn( 'json' );
		WP_Mock::userFunction( 'wp_remote_post' )->
		with(
			API::ENDPOINT,
			[
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'body'        => 'json',
				'data_format' => 'body',
			]
		)->
		once()->
		andReturn(
			[
				//phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				'body' => json_encode(
					[
						'success' => true,
					]
				),
			]
		);
		WP_Mock::userFunction( 'is_wp_error' )->
		once()->
		andReturn( false );

		$api = new API( $db, $settings );

		$this->assertTrue( $api->validate( $api_key ) );
	}

}
