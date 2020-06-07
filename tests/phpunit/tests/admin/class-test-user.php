<?php
/**
 * Admin user tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Actions\expectDone;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_User
 *
 * @package Nova_Poshta\Admin
 */
class Test_User extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$user->hooks();

		$this->assertTrue( has_action( 'shipping_nova_poshta_for_woocommerce_user_fields', [ $user, 'fields' ] ) );
		$this->assertTrue(
			has_action(
				'woocommerce_checkout_create_order_shipping_item',
				[
					$user,
					'checkout',
				]
			)
		);
		$this->assertTrue( has_filter( 'shipping_nova_poshta_for_woocommerce_default_city_id', [ $user, 'city' ] ) );
		$this->assertTrue(
			has_filter(
				'shipping_nova_poshta_for_woocommerce_default_warehouse_id',
				[
					$user,
					'warehouse',
				]
			)
		);
	}

	/**
	 * Test fields
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_fields_for_NOT_registered_users() {
		$user_id         = 10;
		$city_id         = 'city-id';
		$city            = 'City';
		$locale          = 'uk';
		$warehouse_id    = 'warehouse-id-2';
		$warehouses      = [
			'warehouse-id-1' => 'Warehouse',
			$warehouse_id    => 'Warehouse 2',
		];
		$field_city      = [
			'type'     => 'select',
			'label'    => 'Select delivery city',
			'required' => true,
			'options'  => [ $city_id => $city ],
			'default'  => $city_id,
			'priority' => 10,
		];
		$field_warehouse = [
			'type'     => 'select',
			'label'    => 'Choose branch',
			'required' => true,
			'options'  => $warehouses,
			'default'  => $warehouse_id,
			'priority' => 20,
		];
		when( '__' )->returnArg();
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city_id' )
			->with( '', $user_id )
			->once()
			->andReturn( '' );
		expect( 'wp_nonce_field' )
			->with(
				Main::PLUGIN_SLUG . '-shipping',
				'shipping_nova_poshta_for_woocommerce_nonce',
				false
			)
			->once();
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city' )
			->with( $city, $user_id )
			->once()
			->andReturn( $city );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_warehouse_id' )
			->with( $warehouse_id, $user_id, $city )
			->once()
			->andReturn( $warehouse_id );
		expectDone( 'before_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expectDone( 'after_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expectDone( 'before_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_warehouse', $field_warehouse )
			->once();
		expectDone( 'after_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_warehouse', $field_warehouse )
			->once();
		expect( 'woocommerce_form_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expect( 'woocommerce_form_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_warehouse )
			->once();
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->andReturn( [ $city_id => $city ] );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->with( $city_id )
			->andReturn( $warehouses );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->once()
			->andReturn( $locale );

		$user = new User( $api, $language );

		$user->fields();
	}

	/**
	 * Test fields
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_fields_for_registered_users_or_with_POST_request() {
		$user_id         = 10;
		$city_id         = 'city-id';
		$city            = 'City';
		$locale          = 'uk';
		$warehouse_id    = 'warehouse-id-2';
		$warehouses      = [
			'warehouse-id-1' => 'Warehouse',
			$warehouse_id    => 'Warehouse 2',
		];
		$field_city      = [
			'type'     => 'select',
			'label'    => 'Select delivery city',
			'required' => true,
			'options'  => [ $city_id => $city ],
			'default'  => $city_id,
			'priority' => 10,
		];
		$field_warehouse = [
			'type'     => 'select',
			'label'    => 'Choose branch',
			'required' => true,
			'options'  => $warehouses,
			'default'  => $warehouse_id,
			'priority' => 20,
		];
		$api             = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->with( $city_id )
			->once()
			->andReturn( $city );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->with( $city_id )
			->andReturn( $warehouses );
		when( '__' )->returnArg();
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city_id' )
			->with( '', $user_id, $locale )
			->once()
			->andReturn( $city_id );
		expect( 'wp_nonce_field' )
			->with(
				Main::PLUGIN_SLUG . '-shipping',
				'shipping_nova_poshta_for_woocommerce_nonce',
				false
			)
			->once();
		expectDone( 'before_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expectDone( 'after_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expectDone( 'before_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_warehouse', $field_warehouse )
			->once();
		expectDone( 'after_shipping_nova_poshta_for_woocommerce_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_warehouse', $field_warehouse )
			->once();
		expect( 'woocommerce_form_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_city )
			->once();
		expect( 'woocommerce_form_field' )
			->with( 'shipping_nova_poshta_for_woocommerce_city', $field_warehouse )
			->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );

		$user = new User( $api, $language );

		$user->fields();
	}

	/**
	 * Test don't save
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_do_NOT_save_on_checkout_for_not_auth_users() {
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once();

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$user->checkout();
	}

	/**
	 * Test on not valid nonce
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_not_valid_nonce_on_checkout_for_NOT_valid_nonce() {
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( false );
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( 1 );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$user->checkout();
	}

	/**
	 * Test with empty city or warehouse in request
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_not_valid_checkout_with_empty_city_or_warehouse() {
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( 1 );
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( true );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$user->checkout();
	}

	/**
	 * Test valid checkout update user meta fields
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_valid_checkout() {
		global $city_id, $warehouse_id;
		$user_id      = 1;
		$city_id      = 2;
		$warehouse_id = 3;
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( true );

		$filter_input = FunctionMocker::replace(
			'filter_input',
			function () {
				global $city_id, $warehouse_id;
				static $i = 0;

				$answers = [ 'nonce', $city_id, $warehouse_id ];

				return $answers[ $i ++ ];
			}
		);
		expect( 'update_user_meta' )
			->with(
				$user_id,
				'shipping_nova_poshta_for_woocommerce_city',
				$city_id
			)
			->once();
		expect( 'update_user_meta' )
			->with(
				$user_id,
				'shipping_nova_poshta_for_woocommerce_warehouse',
				$warehouse_id
			)
			->once();

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$user->checkout();

		$filter_input->wasCalledWithOnce(
			[
				INPUT_POST,
				'shipping_nova_poshta_for_woocommerce_city',
				FILTER_SANITIZE_STRING,
			]
		);
		$filter_input->wasCalledWithOnce(
			[
				INPUT_POST,
				'shipping_nova_poshta_for_woocommerce_warehouse',
				FILTER_SANITIZE_STRING,
			]
		);
	}

	/**
	 * Test city filter for not auth user
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_city_id_not_auth_user() {
		$user_id = 10;
		$city_id = 'city-id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_user_meta' )
			->with( $user_id, 'shipping_nova_poshta_for_woocommerce_city', true )
			->once()
			->andReturn( $city_id );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $city_id, $user->city( $city_id ) );
	}

	/**
	 * Test city filter for auth user
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_city_id_auth_user() {
		$user_id      = 1;
		$city_id      = 'city-id';
		$user_city_id = 'user-city_id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_user_meta' )
			->with( $user_id, 'shipping_nova_poshta_for_woocommerce_city', true )
			->once()
			->andReturn( $user_city_id );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $user_city_id, $user->city( $city_id ) );
	}

	/**
	 * Test city filter for auth user without city_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_city_id_auth_user_without_city_id() {
		$user_id = 1;
		$city_id = 'city-id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_user_meta' )
			->with( $user_id, 'shipping_nova_poshta_for_woocommerce_city', true )
			->once()
			->andReturn( false );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $city_id, $user->city( $city_id ) );
	}

	/**
	 * Test warehouse filter for not auth user
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouse_id_not_auth_user() {
		$warehouse_id = 'warehouse-id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( 0 );
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $warehouse_id, $user->warehouse( $warehouse_id ) );
	}

	/**
	 * Test warehouse filter for auth user
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouse_id_auth_user() {
		$user_id           = 1;
		$warehouse_id      = 'warehouse-id';
		$user_warehouse_id = 'warehouse-city_id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_user_meta' )
			->with( $user_id, 'shipping_nova_poshta_for_woocommerce_warehouse', true )
			->once()
			->andReturn( $user_warehouse_id );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $user_warehouse_id, $user->warehouse( $warehouse_id ) );
	}

	/**
	 * Test warehouse filter for auth user
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouse_id_auth_user_without_warehouse_id() {
		$user_id      = 1;
		$warehouse_id = 'warehouse-id';
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_user_meta' )
			->with( $user_id, 'shipping_nova_poshta_for_woocommerce_warehouse', true )
			->once()
			->andReturn( false );

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$user     = new User( $api, $language );

		$this->assertSame( $warehouse_id, $user->warehouse( $warehouse_id ) );
	}

}
