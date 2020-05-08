<?php
/**
 * Shipping cost tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Exception;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_API
 *
 * @package Nova_Poshta\Core
 */
class Test_Shipping_Cost extends Test_Case {

	/**
	 * Shipping cost disabled
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_disabled() {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( false );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$cart       = Mockery::mock( 'WC_Cart' );

		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );
		$shipping_cost->calculate( 'city-id', $cart );
	}

	/**
	 * Shipping cost with default product weight and dimensions
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_with_default_weight_and_dimensions() {
		$city_id            = 'city-id';
		$cost               = 48;
		$product1_weight    = 10;
		$product2_weight    = 20;
		$product1_dimension = 15;
		$product2_dimension = 25;
		WP_Mock::userFunction( 'get_option' )->
		with( 'woocommerce_weight_unit' )->
		twice()->
		andReturn( 'weight_unit' );
		WP_Mock::userFunction( 'get_option' )->
		with( 'woocommerce_dimension_unit' )->
		times( 6 )->
		andReturn( 'dimension_unit' );
		WP_Mock::userFunction( 'wc_get_weight' )->
		with( $product1_weight, 'kg', 'weight_unit' )->
		once()->
		andReturn( $product1_weight );
		WP_Mock::userFunction( 'wc_get_weight' )->
		with( $product2_weight, 'kg', 'weight_unit' )->
		once()->
		andReturn( $product2_weight );
		WP_Mock::userFunction( 'wc_get_dimension' )->
		with( $product1_dimension, 'm', 'dimension_unit' )->
		times( 3 )->
		andReturn( $product1_dimension );
		WP_Mock::userFunction( 'wc_get_dimension' )->
		with( $product2_dimension, 'm', 'dimension_unit' )->
		times( 3 )->
		andReturn( $product2_dimension );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'shipping_cost' )
			->with(
				$city_id,
				$product1_weight + $product2_weight,
				( $product1_dimension ^ 3 ) + ( $product2_dimension ^ 3 )
			)
			->once()
			->andReturn( $cost );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product1_dimension . ') * (' . $product1_dimension . ') * (' . $product1_dimension . ')', 1 )
			->once()
			->andReturn( $product1_dimension ^ 3 );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product2_dimension . ') * (' . $product2_dimension . ') * (' . $product2_dimension . ')', 1 )
			->once()
			->andReturn( $product2_dimension ^ 3 );
		$cart     = Mockery::mock( 'WC_Cart' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product1
			->shouldReceive( 'get_weight' )
			->andReturn( $product1_weight )
			->once();
		$product1
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( $product1_dimension )
			->once();
		$product2 = Mockery::mock( 'WC_Product' );
		$product2
			->shouldReceive( 'get_weight' )
			->andReturn( $product2_weight )
			->once();
		$product2
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( $product2_dimension )
			->once();
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn(
				[
					[
						'quantity' => 1,
						'data'     => $product1,
					],
					[
						'quantity' => 1,
						'data'     => $product2,
					],
				]
			);
		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );

		$shipping_cost->calculate( $city_id, $cart );
	}

	/**
	 * Shipping cost with default parent product weight and dimensions. Need for Variable Products
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_with_default_weight_and_dimensions_for_parent_product() {
		$city_id            = 'city-id';
		$cost               = 48;
		$product1_weight    = 10;
		$product2_weight    = 20;
		$product1_dimension = 15;
		$product2_dimension = 25;
		WP_Mock::userFunction( 'get_option' )->
		with( 'woocommerce_weight_unit' )->
		twice()->
		andReturn( 'weight_unit' );
		WP_Mock::userFunction( 'get_option' )->
		with( 'woocommerce_dimension_unit' )->
		times( 6 )->
		andReturn( 'dimension_unit' );
		WP_Mock::userFunction( 'wc_get_weight' )->
		with( $product1_weight, 'kg', 'weight_unit' )->
		once()->
		andReturn( $product1_weight );
		WP_Mock::userFunction( 'wc_get_weight' )->
		with( $product2_weight, 'kg', 'weight_unit' )->
		once()->
		andReturn( $product2_weight );
		WP_Mock::userFunction( 'wc_get_dimension' )->
		with( $product1_dimension, 'm', 'dimension_unit' )->
		times( 3 )->
		andReturn( $product1_dimension );
		WP_Mock::userFunction( 'wc_get_dimension' )->
		with( $product2_dimension, 'm', 'dimension_unit' )->
		times( 3 )->
		andReturn( $product2_dimension );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'shipping_cost' )
			->with(
				$city_id,
				$product1_weight + $product2_weight,
				( $product1_dimension ^ 3 ) + ( $product2_dimension ^ 3 )
			)
			->once()
			->andReturn( $cost );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->once()
			->andReturn( $product1_dimension ^ 3 );
		$calculator
			->shouldReceive( 'result' )
			->once()
			->andReturn( $product2_dimension ^ 3 );
		$cart     = Mockery::mock( 'WC_Cart' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product1
			->shouldReceive( 'get_weight' )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( 10 );
		$product1_parent = Mockery::mock( 'WC_Product' );
		$product1_parent
			->shouldReceive( 'get_weight' )
			->once()
			->andReturn( $product1_weight );
		$product1_parent
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->once()
			->andReturn( $product1_dimension );
		$product2 = Mockery::mock( 'WC_Product' );
		$product2
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( 20 );
		$product2_parent = Mockery::mock( 'WC_Product' );
		$product2_parent
			->shouldReceive( 'get_weight' )
			->once()
			->andReturn( $product2_weight );
		$product2_parent
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->once()
			->andReturn( $product2_dimension );
		WP_Mock::userFunction( 'wc_get_product' )->
		with( 10 )->
		times( 4 )->
		andReturn( $product1_parent );
		WP_Mock::userFunction( 'wc_get_product' )->
		with( 20 )->
		times( 4 )->
		andReturn( $product2_parent );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn(
				[
					[
						'quantity' => 1,
						'data'     => $product1,
					],
					[
						'quantity' => 1,
						'data'     => $product2,
					],
				]
			);
		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );

		$shipping_cost->calculate( $city_id, $cart );
	}

	/**
	 * Shipping cost with product formulas
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_with_product_formula() {
		$city_id                    = 'city-id';
		$cost                       = 48;
		$product1_weight            = 10;
		$product2_weight            = 20;
		$product1_weight_formula    = '10 + [qty]';
		$product2_weight_formula    = '20 + [qty]';
		$product1_dimension         = 15;
		$product2_dimension         = 25;
		$product1_dimension_formula = '15 + [qty]';
		$product2_dimension_formula = '25 + [qty]';
		$api                        = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'shipping_cost' )
			->with(
				$city_id,
				$product1_weight + $product2_weight,
				( $product1_dimension ^ 3 ) + ( $product2_dimension ^ 3 )
			)
			->once()
			->andReturn( $cost );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( $product1_weight_formula, 1 )
			->once()
			->andReturn( $product1_weight );
		$calculator
			->shouldReceive( 'result' )
			->with( $product2_weight_formula, 1 )
			->once()
			->andReturn( $product2_weight );
		$calculator
			->shouldReceive( 'result' )
			->with( $product1_dimension_formula, 1 )
			->times( 3 )
			->andReturn( $product1_dimension );
		$calculator
			->shouldReceive( 'result' )
			->with( $product2_dimension_formula, 1 )
			->times( 3 )
			->andReturn( $product2_dimension );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product1_dimension . ') * (' . $product1_dimension . ') * (' . $product1_dimension . ')', 1 )
			->once()
			->andReturn( $product1_dimension ^ 3 );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product2_dimension . ') * (' . $product2_dimension . ') * (' . $product2_dimension . ')', 1 )
			->once()
			->andReturn( $product2_dimension ^ 3 );
		$cart     = Mockery::mock( 'WC_Cart' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product1
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( $product1_weight_formula );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( $product1_dimension_formula );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( $product1_dimension_formula );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( $product1_dimension_formula );
		$product2 = Mockery::mock( 'WC_Product' );
		$product2
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( $product2_weight_formula );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( $product2_dimension_formula );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( $product2_dimension_formula );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( $product2_dimension_formula );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn(
				[
					[
						'quantity' => 1,
						'data'     => $product1,
					],
					[
						'quantity' => 1,
						'data'     => $product2,
					],
				]
			);
		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );

		$shipping_cost->calculate( $city_id, $cart );
	}

	/**
	 * Shipping cost with product category formulas
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_with_product_category_formula() {
		$city_id                    = 'city-id';
		$cost                       = 48;
		$product1_weight            = 10;
		$product2_weight            = 20;
		$product1_weight_formula    = '10 + [qty]';
		$product2_weight_formula    = '20 + [qty]';
		$product1_dimension         = 15;
		$product2_dimension         = 25;
		$product1_dimension_formula = '15 + [qty]';
		$product2_dimension_formula = '25 + [qty]';
		$category1                  = 100;
		$category2                  = 200;
		$api                        = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'shipping_cost' )
			->with(
				$city_id,
				$product1_weight + $product2_weight,
				( $product1_dimension ^ 3 ) + ( $product2_dimension ^ 3 )
			)
			->once()
			->andReturn( $cost );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( $product1_weight_formula, 1 )
			->once()
			->andReturn( $product1_weight );
		$calculator
			->shouldReceive( 'result' )
			->with( $product2_weight_formula, 1 )
			->once()
			->andReturn( $product2_weight );
		$calculator
			->shouldReceive( 'result' )
			->with( $product1_dimension_formula, 1 )
			->times( 3 )
			->andReturn( $product1_dimension );
		$calculator
			->shouldReceive( 'result' )
			->with( $product2_dimension_formula, 1 )
			->times( 3 )
			->andReturn( $product2_dimension );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product1_dimension . ') * (' . $product1_dimension . ') * (' . $product1_dimension . ')', 1 )
			->once()
			->andReturn( $product1_dimension ^ 3 );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $product2_dimension . ') * (' . $product2_dimension . ') * (' . $product2_dimension . ')', 1 )
			->once()
			->andReturn( $product2_dimension ^ 3 );
		$cart     = Mockery::mock( 'WC_Cart' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product1
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_category_ids' )
			->times( 8 )
			->andReturn( [ $category1 ] );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category1, 'weight_formula', true )->
		once()->
		andReturn( $product1_weight_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category1, 'width_formula', true )->
		once()->
		andReturn( $product1_dimension_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category1, 'height_formula', true )->
		once()->
		andReturn( $product1_dimension_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category1, 'length_formula', true )->
		once()->
		andReturn( $product1_dimension_formula );
		$product2 = Mockery::mock( 'WC_Product' );
		$product2
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_category_ids' )
			->times( 8 )
			->andReturn( [ $category2 ] );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category2, 'weight_formula', true )->
		once()->
		andReturn( $product2_weight_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category2, 'width_formula', true )->
		once()->
		andReturn( $product2_dimension_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category2, 'height_formula', true )->
		once()->
		andReturn( $product2_dimension_formula );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $category2, 'length_formula', true )->
		once()->
		andReturn( $product2_dimension_formula );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn(
				[
					[
						'quantity' => 1,
						'data'     => $product1,
					],
					[
						'quantity' => 1,
						'data'     => $product2,
					],
				]
			);
		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );

		$shipping_cost->calculate( $city_id, $cart );
	}

	/**
	 * Shipping cost with settings formulas
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_shipping_cost_with_default_settings_formula() {
		$city_id                   = 'city-id';
		$cost                      = 48;
		$default_weight            = 10;
		$default_weight_formula    = '10 + [qty]';
		$default_dimension         = 15;
		$default_dimension_formula = '15 + [qty]';
		$api                       = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'shipping_cost' )
			->with(
				$city_id,
				$default_weight + $default_weight,
				( $default_dimension ^ 3 ) + ( $default_dimension ^ 3 )
			)
			->once()
			->andReturn( $cost );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$settings
			->shouldReceive( 'default_weight_formula' )
			->twice()
			->andReturn( $default_weight_formula );
		$settings
			->shouldReceive( 'default_width_formula', 'default_height_formula', 'default_length_formula' )
			->twice()
			->andReturn( $default_dimension_formula );
		$calculator = Mockery::mock( 'Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( $default_weight_formula, 1 )
			->twice()
			->andReturn( $default_weight );
		$calculator
			->shouldReceive( 'result' )
			->with( $default_dimension_formula, 1 )
			->times( 6 )
			->andReturn( $default_dimension );
		$calculator
			->shouldReceive( 'result' )
			->with( '(' . $default_dimension . ') * (' . $default_dimension . ') * (' . $default_dimension . ')', 1 )
			->twice()
			->andReturn( $default_dimension ^ 3 );
		$cart     = Mockery::mock( 'WC_Cart' );
		$product1 = Mockery::mock( 'WC_Product' );
		$product1
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product1
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( false );
		$product1
			->shouldReceive( 'get_category_ids' )
			->times( 4 )
			->andReturn( false );
		$product2 = Mockery::mock( 'WC_Product' );
		$product2
			->shouldReceive( 'get_weight' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_width', 'get_length', 'get_height' )
			->andReturn( false )
			->once();
		$product2
			->shouldReceive( 'get_parent_id' )
			->times( 8 )
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( false );
		$product2
			->shouldReceive( 'get_category_ids' )
			->times( 4 )
			->andReturn( false );
		$cart
			->shouldReceive( 'get_cart_contents' )
			->once()
			->andReturn(
				[
					[
						'quantity' => 1,
						'data'     => $product1,
					],
					[
						'quantity' => 1,
						'data'     => $product2,
					],
				]
			);
		$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );

		$shipping_cost->calculate( $city_id, $cart );
	}

}
