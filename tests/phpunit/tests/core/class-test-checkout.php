<?php
/**
 * Checkout tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;
use WP_Mock\Functions;

/**
 * Class Test_Checkout
 *
 * @package Nova_Poshta\Core
 */
class Test_Checkout extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$checkout = new Checkout();

		WP_Mock::expectActionAdded( 'woocommerce_after_shipping_rate', [ $checkout, 'fields' ] );
		WP_Mock::expectActionAdded( 'woocommerce_checkout_process', [ $checkout, 'validate' ] );

		$checkout->hooks();
	}

	/**
	 * Test fields action
	 */
	public function test_fields() {
		$filter_input = FunctionMocker::replace( 'filter_input', [ 'shipping_nova_poshta_for_woocommerce' ] );
		WP_Mock::userFunction( 'is_checkout' )->
		once()->
		andReturn( true );
		$shipping_rate = Mockery::mock( 'WC_Shipping_Rate' );
		$shipping_rate
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		WP_Mock::expectAction( 'shipping_nova_poshta_for_woocommerce_user_fields' );

		$checkout = new Checkout();
		$checkout->fields( $shipping_rate );

		$filter_input->wasCalledWithOnce(
			[
				INPUT_POST,
				'shipping_method',
				FILTER_SANITIZE_STRING,
				FILTER_REQUIRE_ARRAY,
			]
		);
	}

	/**
	 * Test fields action
	 */
	public function test_fields_on_NOT_checkout_page() {
		WP_Mock::userFunction( 'is_checkout' )->
		once()->
		andReturn( false );
		$shipping_rate = Mockery::mock( 'WC_Shipping_Rate' );
		$checkout      = new Checkout();

		$checkout->fields( $shipping_rate );
	}

	/**
	 * Test validation with invalid nonce
	 */
	public function test_validation_invalid_nonce() {
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		once()->
		andReturn( false );

		$checkout = new Checkout();
		$checkout->validate();
	}

	/**
	 * Test validation show notices with empty city and warehouse
	 */
	public function test_validation() {
		$_POST['shipping_nova_poshta_for_woocommerce_city']      = '';
		$_POST['shipping_nova_poshta_for_woocommerce_warehouse'] = '';
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		once()->
		andReturn( true );
		WP_Mock::userFunction( 'wc_add_notice' )->
		twice()->
		withArgs( [ Functions::type( 'string' ), 'error' ] );

		$checkout = new Checkout();
		$checkout->validate();
	}

}
