<?php
/**
 * Shipping
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

/**
 * Class Shipping
 *
 * @package Nova_Poshta\Core
 */
class Shipping {

	/**
	 * Shipping method name
	 *
	 * @var string
	 */
	private $method_name = 'woo_nova_poshta';

	/**
	 * Require shipping methods
	 */
	public function require_methods() {
		require_once plugin_dir_path( __DIR__ ) . 'shipping/class-nova-poshta-shipping-method.php';
	}

	/**
	 * Register shipping method
	 *
	 * @param array $methods Shipping methods.
	 *
	 * @return array
	 */
	public function register_methods( array $methods ): array {
		$methods[ $this->method_name ] = 'Nova_Poshta_Shipping_Method';

		return $methods;
	}

	/**
	 * Is shipping method active
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		global $wpdb;
		$is_active = wp_cache_get( $this->method_name . '_active' );
		if ( null !== $is_active ) {
			//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$is_active = (bool) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = %s AND `is_enabled` = 1 LIMIT 1',
					$this->method_name
				)
			);
			wp_cache_set( $this->method_name . '_active', $is_active );
		}

		return $is_active;
	}

}
