<?php
/**
 * Transient cache for Nova Poshta
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
 * Class Transient_Cache
 *
 * @package Nova_Poshta\Core
 */
class Transient_Cache extends Abstract_Cache {

	/**
	 * Transient_Cache constructor.
	 */
	public function __construct() {
		parent::__construct( __CLASS__ );
	}

	/**
	 * Set value for cache with key.
	 *
	 * @param string $key    Key name.
	 * @param mixed  $value  Value.
	 * @param int    $expire Expire in seconds.
	 */
	public function set( string $key, $value, int $expire ) {
		$this->add_key( $key );
		set_transient( Main::PLUGIN_SLUG . '-' . $key, $value, $expire );
	}

	/**
	 * Get cache by key name.
	 *
	 * @param string $key Key name.
	 *
	 * @return bool|mixed
	 */
	public function get( string $key ) {
		return get_transient( Main::PLUGIN_SLUG . '-' . $key );
	}

	/**
	 * Delete cache by key name.
	 *
	 * @param string $key Key name.
	 */
	public function delete( string $key ) {
		delete_transient( Main::PLUGIN_SLUG . '-' . $key );
	}

}
