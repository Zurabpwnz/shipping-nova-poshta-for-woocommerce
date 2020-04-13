<?php
/**
 * Checkout tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

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
		$filter_input  = FunctionMocker::replace( 'filter_input', [ 'woo_nova_poshta' ] );
		$shipping_rate = \Mockery::mock( 'WC_Shipping_Rate' );
		$shipping_rate
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'woo_nova_poshta' );
		WP_Mock::expectAction( 'woo_nova_poshta_user_fields' );

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
		$_POST['woo_nova_poshta_city']      = '';
		$_POST['woo_nova_poshta_warehouse'] = '';
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
