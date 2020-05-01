<?php
/**
 * Object cache for Nova Poshta
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core\Cache;

use Nova_Poshta\Core\Main;

/**
 * Class Object_Cache
 *
 * @package Nova_Poshta\Core
 */
class Object_Cache extends Abstract_Cache {

	/**
	 * Object_Cache constructor.
	 */
	public function __construct() {
		parent::__construct( __CLASS__ );
	}

	/**
	 * Set value for cache with key.
	 *
	 * @param string $key   Key name.
	 * @param mixed  $value Value.
	 */
	public function set( string $key, $value ) {
		$this->add_key( $key );
		wp_cache_set( $key, $value, Main::PLUGIN_SLUG );
	}

	/**
	 * Get cache value by name
	 *
	 * @param string $key Key name.
	 *
	 * @return bool|mixed
	 */
	public function get( string $key ) {
		return wp_cache_get( $key, Main::PLUGIN_SLUG );
	}

	/**
	 * Delete cache by key name.
	 *
	 * @param string $key Key name.
	 */
	public function delete( string $key ) {
		$this->delete_key( $key );
		wp_cache_delete( $key, Main::PLUGIN_SLUG );
	}

}
