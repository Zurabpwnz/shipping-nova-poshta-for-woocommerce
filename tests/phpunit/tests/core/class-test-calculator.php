<?php
/**
 * Calculator tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Ajax
 *
 * @package Nova_Poshta\Core
 */
class Test_Calculator extends Test_Case {

	/**
	 * Test result
	 */
	public function test_result() {
		FunctionMocker::replace( 'class_exists', true );
		WP_Mock::userFunction( 'wc_get_price_decimal_separator' )->
		once()->
		andReturn( '.' );
		$c = new Calculator();

		$this->assertSame( 10.0, $c->result( '5 + 10', 15 ) );
	}

}
