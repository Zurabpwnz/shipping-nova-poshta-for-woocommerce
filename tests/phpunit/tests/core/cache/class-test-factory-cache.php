<?php
/**
 * Factory cache tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core\Cache;

use Mockery;
use Nova_Poshta\Tests\Test_Case;

/**
 * Class Object_Cache
 *
 * @package Nova_Poshta\Core
 */
class Test_Factory_Cache extends Test_Case {

	/**
	 * Test transient cache
	 */
	public function test_transient() {
		$transient_cache = Mockery::mock( '\Nova_Poshta\Core\Cache\Transient_Cache' );
		$object_cache    = Mockery::mock( '\Nova_Poshta\Core\Cache\Object_Cache' );
		$factory_cache   = new Factory_Cache( $transient_cache, $object_cache );

		$this->assertSame( $transient_cache, $factory_cache->transient() );
	}

	/**
	 * Test object cache
	 */
	public function test_object() {
		$transient_cache = Mockery::mock( '\Nova_Poshta\Core\Cache\Transient_Cache' );
		$object_cache    = Mockery::mock( '\Nova_Poshta\Core\Cache\Object_Cache' );
		$factory_cache   = new Factory_Cache( $transient_cache, $object_cache );

		$this->assertSame( $object_cache, $factory_cache->object() );
	}

}
