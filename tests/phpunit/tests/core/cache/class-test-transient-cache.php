<?php
/**
 * Object cache tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core\Cache;

use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Object_Cache
 *
 * @package Nova_Poshta\Core
 */
class Test_Transient_Cache extends Test_Case {

	/**
	 * Test set new object cache
	 */
	public function test_set() {
		$key     = 'some-key';
		$value   = 'value';
		$exprire = 100;
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys', Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'wp_cache_set' )->
		with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys', [ $key ], Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'set_transient' )->
		with( Main::PLUGIN_SLUG . '-' . $key, $value, $exprire )->
		once();
		$object_cache = new Transient_Cache();

		$object_cache->set( $key, $value, $exprire );
	}

	/**
	 * Get cache object by key
	 */
	public function test_get() {
		$key = 'some-key';
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys', Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'get_transient' )->
		with( Main::PLUGIN_SLUG . '-' . $key )->
		once();
		$object_cache = new Transient_Cache();

		$object_cache->get( $key );
	}

	/**
	 * Delete object cache by key
	 */
	public function test_delete() {
		$key = 'some-key';
		WP_Mock::userFunction( 'wp_cache_get' )->
		with( 'Nova_Poshta\Core\Cache\Transient_Cache-keys', Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'delete_transient' )->
		with( Main::PLUGIN_SLUG . '-' . $key )->
		once();
		$object_cache = new Transient_Cache();

		$object_cache->delete( $key );
	}

}
