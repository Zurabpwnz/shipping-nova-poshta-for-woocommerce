<?php
/**
 * Test case
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Case
 */
abstract class Test_Case extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp(): void {
		FunctionMocker::setUp();
		parent::setUp();
		WP_Mock::setUp(); // Must be the last one.
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

}
