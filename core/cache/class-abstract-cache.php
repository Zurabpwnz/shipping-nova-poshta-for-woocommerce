<?php
/**
 * Abstract Cache for Nova Poshta
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
 * Class Cache
 *
 * @package Nova_Poshta\Core
 */
abstract class Abstract_Cache {

	/**
	 * List of plugin keys
	 *
	 * @var array
	 */
	protected $keys;
	/**
	 * Key name
	 *
	 * @var string
	 */
	private $key_name;

	/**
	 * Cache constructor.
	 *
	 * @param string $key_name Name for keys.
	 */
	public function __construct( string $key_name ) {
		$this->key_name = $key_name . '-keys';
		$keys           = wp_cache_get( $this->key_name, Main::PLUGIN_SLUG );
		$this->keys     = ! empty( $keys ) && is_array( $keys ) ? $keys : [];
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		register_deactivation_hook(
			plugin_dir_path( __DIR__ ) . dirname( plugin_basename( __DIR__ ) ) . '.php',
			[ $this, 'flush' ]
		);
	}

	/**
	 * Add key from plugin keys
	 *
	 * @param string $key Key name.
	 */
	protected function add_key( string $key ) {
		$this->keys[] = $key;
		$this->save_keys();
	}

	/**
	 * Delete key from plugin keys
	 *
	 * @param string $key Key name.
	 */
	protected function delete_key( string $key ) {
		unset( $this->keys[ $key ] );
		$this->save_keys();
	}

	/**
	 * Save keys
	 */
	protected function save_keys() {
		$this->keys = array_unique( $this->keys );
		wp_cache_set( $this->key_name, $this->keys, Main::PLUGIN_SLUG );
	}

	/**
	 * Set value for cache with key.
	 *
	 * @param string $key   Key name.
	 * @param mixed  $value Value.
	 */
	abstract public function set( string $key, $value );

	/**
	 * Get cache value by name
	 *
	 * @param string $key Key name.
	 *
	 * @return bool|mixed
	 */
	abstract public function get( string $key );

	/**
	 * Delete cache by key name.
	 *
	 * @param string $key Key name.
	 */
	abstract public function delete( string $key );

	/**
	 * Flush all plugin cache
	 */
	public function flush() {
		foreach ( $this->keys as $key ) {
			$this->delete( $key );
		}
		$this->delete( $this->key_name );
	}

}
