<?php
/**
 * Object cache tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core\Cache;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use function Brain\Monkey\Functions\expect;

/**
 * Class Object_Cache
 *
 * @package Nova_Poshta\Core
 */
class Test_Object_Cache extends Test_Case {

	/**
	 * Test set new object cache
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_set() {
		$key    = 'some-key';
		$value  = 'value';
		$expire = 100;
		expect( 'get_transient' )
			->with( 'Nova_Poshta\Core\Cache\Object_Cache-keys' )
			->once();
		expect( 'set_transient' )
			->with( 'Nova_Poshta\Core\Cache\Object_Cache-keys', [ $key ] )
			->once();
		expect( 'wp_cache_set' )
			->with( $key, $value, Main::PLUGIN_SLUG, $expire )
			->once();
		$object_cache = new Object_Cache();

		$object_cache->set( $key, $value, $expire );
	}

	/**
	 * Get cache object by key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_get() {
		$key = 'some-key';
		expect( 'get_transient' )
			->with( 'Nova_Poshta\Core\Cache\Object_Cache-keys' )
			->once();
		expect( 'wp_cache_get' )
			->with( $key, Main::PLUGIN_SLUG )
			->once();
		$object_cache = new Object_Cache();

		$object_cache->get( $key );
	}

	/**
	 * Delete object cache by key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_delete() {
		$key = 'some-key';
		expect( 'get_transient' )
			->with( 'Nova_Poshta\Core\Cache\Object_Cache-keys' )
			->once();
		expect( 'wp_cache_delete' )
			->with( $key, Main::PLUGIN_SLUG )
			->once();
		expect( 'set_transient' )
			->with( $key, Main::PLUGIN_SLUG )
			->once();

		$object_cache = new Object_Cache();

		$object_cache->delete( $key );
	}

}
