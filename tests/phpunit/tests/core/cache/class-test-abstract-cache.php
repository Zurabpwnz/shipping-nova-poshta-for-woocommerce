<?php
/**
 * Abstract cache tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core\Cache;

use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use ReflectionException;
use WP_Mock;

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
	 */
	public function test_flush() {
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( 'prefix-keys', Main::PLUGIN_SLUG )->
		once();
		$stub = $this->getMockForAbstractClass( 'Nova_Poshta\Core\Cache\Abstract_Cache', [ 'prefix' ] );
		$this->update_inaccessible_property( $stub, 'keys', [ 'key-1', 'key-2' ] );
		$stub
			->expects( $this->exactly( 3 ) )
			->method( 'delete' )
			->withAnyParameters();

		$stub->flush();
	}

	/**
	 * Test hooks
	 */
	public function test_hooks() {
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( 'prefix-keys', Main::PLUGIN_SLUG )->
		once();
		$stub = $this->getMockForAbstractClass( 'Nova_Poshta\Core\Cache\Abstract_Cache', [ 'prefix' ] );
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once();
		WP_Mock::userFunction( 'plugin_basename' )->
		once()->
		andReturn( 'path/to/main-file' );
		WP_Mock::userFunction( 'register_deactivation_hook' )->
		once();

		$stub->hooks();
	}

}
