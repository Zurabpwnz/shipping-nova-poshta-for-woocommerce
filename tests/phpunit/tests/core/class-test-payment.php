<?php
/**
 * Payment tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;

/**
 * Class Test_Shipping
 *
 * @package Nova_Poshta\Core
 */
class Test_Payment extends Test_Case {

	/**
	 * Test hooks
	 */
	public function test_hooks() {
		$payment = new Payment();
		$payment->hooks();

		has_action( 'plugins_loaded', [ $payment, 'require_methods' ] );
		has_filter( 'woocommerce_payment_gateways', [ $payment, 'register_methods' ] );
	}

	/**
	 * Test register payment method
	 */
	public function test_register_methods() {
		$payment = new Payment();

		$this->assertSame(
			[ Payment::METHOD_NAME => 'Nova_Poshta_Gateway_COD' ],
			$payment->register_methods( [] )
		);
	}

}
