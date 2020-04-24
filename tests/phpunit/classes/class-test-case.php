<?php
/**
 * Test case
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Case
 */
abstract class Test_Case extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp() {
		FunctionMocker::setUp();
		parent::setUp();
		WP_Mock::setUp(); // Must be the last one.
	}

	/**
	 * End test
	 */
	public function tearDown() {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	/**
	 * Set an object protected property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function set_protected_property( $object, string $property_name, $value ) {
		$reflection_class = new ReflectionClass( $object );

		$property = $reflection_class->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
		$property->setAccessible( false );
	}

}
