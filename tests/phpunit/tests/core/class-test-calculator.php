<?php
/**
 * Calculator tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Ajax
 *
 * @package Nova_Poshta\Core
 */
class Test_Calculator extends Test_Case {

	/**
	 * Test result
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_result() {
		FunctionMocker::replace( 'class_exists', true );
		expect( 'wc_get_price_decimal_separator' )
			->withNoArgs()
			->once()
			->andReturn( '.' );
		$c = new Calculator();

		$this->assertSame( 10.0, $c->result( '5 + 10', 15 ) );
	}

}
