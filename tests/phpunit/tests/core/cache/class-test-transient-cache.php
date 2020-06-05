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
use WP_Mock;
use function Brain\Monkey\Functions\expect;

/**
 * Class Object_Cache
 *
 * @package Nova_Poshta\Core
 */
class Test_Transient_Cache extends Test_Case {

	/**
	 * Test set new object cache
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_set() {
		$key     = 'some-key';
		$value   = 'value';
		$exprire = 100;
		expect( 'get_transient' )
			->with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys' )
			->once();
		expect( 'set_transient' )
			->with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys', [ $key ] )
			->once();
		expect( 'set_transient' )
			->with( Main::PLUGIN_SLUG . '-' . $key, $value, $exprire )
			->once();
		$object_cache = new Transient_Cache();

		$object_cache->set( $key, $value, $exprire );
	}

	/**
	 * Get cache object by key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_get() {
		$key = 'some-key';
		expect( 'get_transient' )
			->with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys' )
			->once();
		expect( 'get_transient' )
			->with( Main::PLUGIN_SLUG . '-' . $key )
			->once();
		$object_cache = new Transient_Cache();

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
			->with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys' )
			->once();
		expect( 'delete_transient' )
			->with( Main::PLUGIN_SLUG . '-' . $key )
			->once();
		$object_cache = new Transient_Cache();

		$object_cache->delete( $key );
	}

}
