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
		$methods['woo_nova_poshta'] = 'Nova_Poshta_Shipping_Method';

		return $methods;
	}

}
