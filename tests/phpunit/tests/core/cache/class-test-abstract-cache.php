<?php
/**
 * Abstract cache tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core\Cache;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Nova_Poshta\Tests\Test_Case;
use ReflectionException;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Abstract_Cache
 *
 * @package Nova_Poshta\Core
 */
class Test_Abstract_Cache extends Test_Case {

	/**
	 * Test flush cache
	 *
	 * @throws ReflectionException Invalid property.
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_flush() {
		expect( 'get_transient' )
			->with( 'prefix-keys' )
			->once();
		global $times;
		$times = 0;
		$stub  = new class( 'prefix' ) extends Abstract_Cache {

			/**
			 * Delete cache by key name.
			 *
			 * @param string $key Key name.
			 */
			public function delete( string $key ) {
				global $times;
				$times ++;
			}

			/**
			 * Set value for cache with key.
			 *
			 * @param string $key    Key name.
			 * @param mixed  $value  Value.
			 * @param int    $expire Expire of a cache.
			 */
			public function set( string $key, $value, int $expire ) {
				// TODO: Implement set() method.
			}

			/**
			 * Get cache value by name
			 *
			 * @param string $key Key name.
			 *
			 * @return bool|mixed
			 */
			public function get( string $key ) {
				return false;
			}

		};
		$this->update_inaccessible_property( $stub, 'keys', [ 'key-1', 'key-2' ] );
		expect( 'delete_transient' )
			->with( 'prefix-keys' )
			->once();

		$stub->flush();

		$this->assertSame( 2, $times );
		unset( $times );
	}

	/**
	 * Test hooks
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_hooks() {
		expect( 'get_transient' )
			->with( 'prefix-keys' )
			->once();
		$stub = new class( 'prefix' ) extends Abstract_Cache {

			/**
			 * Delete cache by key name.
			 *
			 * @param string $key Key name.
			 */
			public function delete( string $key ) {
				// TODO: Implement delete() method.
			}

			/**
			 * Set value for cache with key.
			 *
			 * @param string $key    Key name.
			 * @param mixed  $value  Value.
			 * @param int    $expire Expire of a cache.
			 */
			public function set( string $key, $value, int $expire ) {
				// TODO: Implement set() method.
			}

			/**
			 * Get cache value by name
			 *
			 * @param string $key Key name.
			 *
			 * @return bool|mixed
			 */
			public function get( string $key ) {
				return false;
			}

		};
		expect( 'plugin_dir_path' )
			->withAnyArgs()
			->once();
		expect( 'plugin_basename' )
			->withAnyArgs()
			->once()
			->andReturn( 'path/to/main-file' );
		expect( 'register_deactivation_hook' )
			->with(
				'path/to/main-file.php',
				[ $stub, 'flush' ]
			)
			->once();

		$stub->hooks();
	}

}
