<?php
/**
 * Ajax tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Ajax
 *
 * @package Nova_Poshta\Core
 */
class Test_Ajax extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->hooks();

		$this->assertTrue(
			has_action( 'wp_ajax_shipping_nova_poshta_for_woocommerce_city', [ $ajax, 'cities' ] )
		);
		$this->assertTrue(
			has_action(
				'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_city',
				[
					$ajax,
					'cities',
				]
			)
		);
		$this->assertTrue(
			has_action(
				'wp_ajax_shipping_nova_poshta_for_woocommerce_warehouse',
				[
					$ajax,
					'warehouses',
				]
			)
		);
		$this->assertTrue(
			has_action(
				'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_warehouse',
				[
					$ajax,
					'warehouses',
				]
			)
		);
	}

	/**
	 * Test search cities
	 */
	public function test_cities() {
		$city_id   = 'city_id';
		$city_name = 'City Name';
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )
			->once();
		expect( 'wp_send_json' )
			->with(
				[
					[
						'id'   => $city_id,
						'text' => $city_name,
					],
				]
			)
			->once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_name );
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->with( $city_name, 10 )
			->andReturn( [ $city_id => $city_name ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->cities();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'search', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Test search city warehouses
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouses() {
		$city_id        = 'city_id';
		$warehouse_id   = 'warehouse_id';
		$warehouse_name = 'City Name';
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )
			->once();
		expect( 'wp_send_json' )
			->with(
				[
					[
						'id'   => $warehouse_id,
						'text' => $warehouse_name,
					],
				]
			)
			->once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->with( $city_id )
			->andReturn( [ $warehouse_id => $warehouse_name ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->warehouses();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Shipping cost without city_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_cost_without_city_id() {
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )
			->once();
		expect( 'wp_send_json_error' )->
		once();
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
	}

	/**
	 * Shipping cost without cart
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_cost_without_cart() {
		$city_id = 'city_id';
		FunctionMocker::replace( 'filter_input', $city_id );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = null;
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )->
			once();
		expect( 'wp_send_json_error' )
			->withNoArgs()
			->once();
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
	}

	/**
	 * Test shipping cost without contents
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_without_contents() {
		$city_id = 'city_id';
		$price   = 48;
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )
			->once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$cart         = Mockery::mock( 'WC_Cart' );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn( [] );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost     = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		expect( 'wp_send_json_error' )
			->withNoArgs()
			->once();

		$ajax = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Test shipping cost
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_cost() {
		$city_id    = 'city_id';
		$price      = 48;
		$price_html = '<span>' . $price . '</span>';
		expect( 'check_ajax_referer' )
			->with( Main::PLUGIN_SLUG, 'nonce' )
			->once();
		expect( 'wp_send_json' )
			->with( $price_html )
			->once();
		expect( 'wc_price' )
			->with( $price )
			->once()
			->andReturn( $price_html );
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$contents     = [
			[
				'quantity' => 1,
				'data'     => 'item1',
			],
			[
				'quantity' => 3,
				'data'     => 'item2',
			],
		];
		$cart         = Mockery::mock( 'WC_Cart' );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn( $contents );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost     = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with( $city_id, $contents )
			->once()
			->andReturn( $price );
		$ajax = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

}
