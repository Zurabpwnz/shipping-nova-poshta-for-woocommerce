<?php
/**
 * Ajax tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

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

		WP_Mock::expectActionAdded( 'wp_ajax_shipping_nova_poshta_for_woocommerce_city', [ $ajax, 'cities' ] );
		WP_Mock::expectActionAdded( 'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_city', [ $ajax, 'cities' ] );
		WP_Mock::expectActionAdded( 'wp_ajax_shipping_nova_poshta_for_woocommerce_warehouse', [ $ajax, 'warehouses' ] );
		WP_Mock::expectActionAdded(
			'wp_ajax_nopriv_shipping_nova_poshta_for_woocommerce_warehouse',
			[
				$ajax,
				'warehouses',
			]
		);

		$ajax->hooks();
	}

	/**
	 * Test search cities
	 */
	public function test_cities() {
		$city_id   = 'city_id';
		$city_name = 'City Name';
		WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		WP_Mock::userFunction( 'wp_send_json' )->
		withArgs(
			[
				[
					[
						'id'   => $city_id,
						'text' => $city_name,
					],
				],
			]
		)->
		once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_name );
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->withArgs( [ $city_name, 10 ] )
			->andReturn( [ $city_id => $city_name ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->cities();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'search', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Test search city warehouses
	 */
	public function test_warehouses() {
		$city_id        = 'city_id';
		$warehouse_id   = 'warehouse_id';
		$warehouse_name = 'City Name';
		WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		WP_Mock::userFunction( 'wp_send_json' )->
		withArgs(
			[
				[
					[
						'id'   => $warehouse_id,
						'text' => $warehouse_name,
					],
				],
			]
		)->
		once();
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->withArgs( [ $city_id ] )
			->andReturn( [ $warehouse_id => $warehouse_name ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->warehouses();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Shipping cost without city_id
	 */
	public function test_shipping_cost_without_city_id() {
		WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		WP_Mock::userFunction( 'wp_send_json_error' )->
		once();
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
	}

	/**
	 * Shipping cost without cart
	 */
	public function test_shipping_cost_without_cart() {
		$city_id = 'city_id';
		FunctionMocker::replace( 'filter_input', $city_id );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = null;
		WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		WP_Mock::userFunction( 'wp_send_json_error' )->
		once();
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$ajax          = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
	}

	/**
	 * Test shipping cost
	 */
	public function test_shipping_cost() {
		$city_id    = 'city_id';
		$price      = 48;
		$price_html = '<span>' . $price . '</span>';
		WP_Mock::userFunction( 'check_ajax_referer' )->
		withArgs( [ Main::PLUGIN_SLUG, 'nonce' ] )->
		once();
		WP_Mock::userFunction( 'wp_send_json' )->
		with( $price_html )->
		once();
		WP_Mock::userFunction( 'wc_price' )->
		with( $price )->
		once()->
		andReturn( $price_html );
		$filter_input = FunctionMocker::replace( 'filter_input', $city_id );
		$cart         = Mockery::mock( 'WC_Cart' );
		global $woocommerce;
		$woocommerce       = new stdClass();
		$woocommerce->cart = $cart;
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost     = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with( $city_id, $cart )
			->once()
			->andReturn( $price );
		$ajax = new AJAX( $api, $shipping_cost );

		$ajax->shipping_cost();
		$filter_input->wasCalledWithOnce( [ INPUT_POST, 'city', FILTER_SANITIZE_STRING ] );
	}

}
