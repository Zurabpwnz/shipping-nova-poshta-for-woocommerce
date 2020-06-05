<?php
/**
 * Checkout tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Actions\expectDone;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

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

		$checkout->hooks();

		$this->assertTrue( has_action( 'woocommerce_after_shipping_rate', [ $checkout, 'fields' ] ) );
		$this->assertTrue( has_action( 'woocommerce_checkout_process', [ $checkout, 'validate' ] ) );
	}

	/**
	 * Test fields action
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_fields() {
		$filter_input = FunctionMocker::replace( 'filter_input', [ 'shipping_nova_poshta_for_woocommerce' ] );
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expectDone( 'shipping_nova_poshta_for_woocommerce_user_fields' )
			->withNoArgs()
			->once();
		$shipping_rate = Mockery::mock( 'WC_Shipping_Rate' );
		$shipping_rate
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
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
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_fields_on_NOT_checkout_page() {
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$shipping_rate = Mockery::mock( 'WC_Shipping_Rate' );
		$checkout      = new Checkout();

		$checkout->fields( $shipping_rate );
	}

	/**
	 * Test validation with invalid nonce
	 */
	public function test_validation_invalid_nonce() {
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( false );

		$checkout = new Checkout();
		$checkout->validate();
	}

	/**
	 * Test validation show notices with empty city and warehouse
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_validation() {
		$_POST['shipping_nova_poshta_for_woocommerce_city']      = '';
		$_POST['shipping_nova_poshta_for_woocommerce_warehouse'] = '';
		when( '__' )->returnArg();
		expect( 'wp_verify_nonce' )
			->with()
			->once()
			->andReturn( true );
		expect( 'wc_add_notice' )
			->with( Mockery::type( 'string' ), 'error' )
			->twice();

		$checkout = new Checkout();
		$checkout->validate();
	}

}
