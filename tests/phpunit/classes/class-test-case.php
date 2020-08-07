<?php
/**
 * Test case
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Tests;

use ReflectionMethod;
use ReflectionProperty;
use ReflectionException;
use PHPUnit\Framework\TestCase;
use tad\FunctionMocker\FunctionMocker;

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

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
		setUp();
	}

	/**
	 * End test
	 */
	public function tearDown() {
		tearDown();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	/**
	 * Read inaccessible property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 * @throws ReflectionException Reflection exception.
	 */
	protected function read_inaccessible_property( $object, string $property_name ) {
		$property = new ReflectionProperty( $object, $property_name );
		$property->setAccessible( true );
		$value = $property->getValue( $object );
		$property->setAccessible( false );

		return $value;
	}

	/**
	 * Set an object inaccessible property.
	 *
	 * @param object $object        Object.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Property vale.
	 *
	 * @throws ReflectionException Reflection exception.
	 */
	protected function update_inaccessible_property( $object, string $property_name, $value ) {
		$property = new ReflectionProperty( $object, $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
		$property->setAccessible( false );
	}

	/**
	 * Run a protected/private methods.
	 *
	 * @param object $object      Object.
	 * @param string $method_name Method name.
	 * @param array  $args        Method arguments.
	 *
	 * @return mixed
	 * @throws ReflectionException Reflection exception.
	 */
	protected function run_inaccesible_method( $object, string $method_name, array $args = [] ) {
		$method = new ReflectionMethod( $object, $method_name );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $object, $args );
		$method->setAccessible( false );

		return $result;
	}

}
