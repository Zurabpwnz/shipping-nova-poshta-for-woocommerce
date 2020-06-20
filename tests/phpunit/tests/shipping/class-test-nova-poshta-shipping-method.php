<?php
/**
 * Nova_Poshta_Shipping_Method tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_Thank_You
 *
 * @package Nova_Poshta\Shipping
 */
class Test_Nova_Poshta_Shipping_Method extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test___construct() {
		when( '__' )->returnArg();
		$nova_poshta_shipping_method = new Nova_Poshta_Shipping_Method();

		$this->assertSame( 'shipping_nova_poshta_for_woocommerce', $nova_poshta_shipping_method->id );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_description );
		$this->assertSame( 'yes', $nova_poshta_shipping_method->enabled );
		$this->assertSame(
			[
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			],
			$nova_poshta_shipping_method->supports
		);
		$this->assertSame(
			[
				'title' => [
					'title'   => 'Method header',
					'type'    => 'text',
					'default' => 'Nova Poshta delivery',
				],
			],
			$nova_poshta_shipping_method->instance_form_fields
		);
	}

	/**
	 * Test save action
	 */
	public function test_save_action() {
		when( '__' )->returnArg();
		$stub     = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id = 'shipping_nova_poshta_for_woocommerce';

		$stub->init();

		$this->assertTrue(
			has_action(
				'woocommerce_update_options_shipping_shipping_nova_poshta_for_woocommerce',
				[
					$stub,
					'process_admin_options',
				]
			)
		);
	}

	/**
	 * Test calculate shipping
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_calculate_shipping_with_default_city() {
		$user_id = 'user-id';
		$city_id = 'city-id';
		$city    = 'City';
		$locale  = 'ua';
		$cost    = 48;
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( false );
		Mockery::mock( 'overload:Nova_Poshta\Admin\Notice\Notice' );
		$language = Mockery::mock( 'overload:Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->once()
			->andReturn( $locale );
		Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Object_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Transient_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Settings' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product2 = Mockery::mock( 'WC_Product' );
		$api      = Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->andReturn( [ $city_id => $city ] );
		Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		$cart = Mockery::mock( 'WC_Cart' );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$contents          = [
			[
				'quantity' => 10,
				'data'     => $product1,
			],
			[
				'quantity' => 15,
				'data'     => $product2,
			],
		];
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn( $contents );
		$shipping_cost = Mockery::mock( 'overload:Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with( $city_id, $contents )
			->once()
			->andReturn( $cost );
		$stub        = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id    = 'shipping_nova_poshta_for_woocommerce';
		$stub->title = 'shipping_nova_poshta_for_woocommerce';
		$stub
			->shouldReceive( 'add_rate' )
			->once()
			->with(
				[
					'id'       => $stub->id,
					'label'    => $stub->title,
					'cost'     => $cost,
					'calc_tax' => 'per_item',
				]
			);

		$stub->calculate_shipping();
	}

	/**
	 * Test calculate shipping for register users with city_id
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_calculate_shipping_with_recipient_city() {
		$user_id = 10;
		$cost    = 48;
		$city_id = 'city-id';
		expect( 'get_current_user_id' )->
		once()->
		andReturn( $user_id );
		expect( 'wp_verify_nonce' )
			->with( null, Main::PLUGIN_SLUG . '-shipping' )
			->once()
			->andReturn( false );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city_id' )
			->with( '', $user_id )
			->once()
			->andReturn( $city_id );
		Mockery::mock( 'overload:Nova_Poshta\Admin\Notice\Notice' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Language' );
		Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Object_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Transient_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Settings' );
		Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product2 = Mockery::mock( 'WC_Product' );
		$cart     = Mockery::mock( 'WC_Cart' );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$contents          = [
			[
				'quantity' => 10,
				'data'     => $product1,
			],
			[
				'quantity' => 15,
				'data'     => $product2,
			],
		];
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn( $contents );
		$shipping_cost = Mockery::mock( 'overload:Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with( $city_id, $contents )
			->once()
			->andReturn( $cost );
		$stub        = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id    = 'shipping_nova_poshta_for_woocommerce';
		$stub->title = 'shipping_nova_poshta_for_woocommerce';
		$stub
			->shouldReceive( 'add_rate' )
			->once()
			->with(
				[
					'id'       => $stub->id,
					'label'    => $stub->title,
					'cost'     => $cost,
					'calc_tax' => 'per_item',
				]
			);

		$stub->calculate_shipping();
	}

	/**
	 * Test calculate shipping via POST-request
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_calculate_shipping_with_city_request() {
		$user_id         = 10;
		$cost            = 48;
		$nonce           = 'nonce';
		$city_id         = 'city-id';
		$request_city_id = 'request-city-id';
		expect( 'get_current_user_id' )
			->once()
			->andReturn( $user_id );
		expect( 'wp_verify_nonce' )
			->with( $nonce, Main::PLUGIN_SLUG . '-shipping' )
			->once()
			->andReturn( true );
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city_id' )
			->with( '', $user_id )
			->once()
			->andReturn( $city_id );
		FunctionMocker::replace(
			'filter_input',
			function () use ( $nonce, $request_city_id ) {
				static $i = 0;

				$answers = [ $nonce, $request_city_id ];

				return $answers[ $i ++ ];
			}
		);
		Mockery::mock( 'overload:Nova_Poshta\Admin\Notice\Notice' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Language' );
		Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Object_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Transient_Cache' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Settings' );
		Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product2 = Mockery::mock( 'WC_Product' );
		$cart     = Mockery::mock( 'WC_Cart' );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$contents          = [
			[
				'quantity' => 10,
				'data'     => $product1,
			],
			[
				'quantity' => 15,
				'data'     => $product2,
			],
		];
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn( $contents );
		$shipping_cost = Mockery::mock( 'overload:Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with( $request_city_id, $contents )
			->once()
			->andReturn( $cost );
		$stub        = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id    = 'shipping_nova_poshta_for_woocommerce';
		$stub->title = 'shipping_nova_poshta_for_woocommerce';
		$stub
			->shouldReceive( 'add_rate' )
			->once()
			->with(
				[
					'id'       => $stub->id,
					'label'    => $stub->title,
					'cost'     => $cost,
					'calc_tax' => 'per_item',
				]
			);

		$stub->calculate_shipping();
	}

	/**
	 * Test process admin options.
	 */
	public function test_process_admin_options() {
		$stub = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();

		$this->assertFalse( $stub->process_admin_options() );
	}

}
