<?php
/**
 * Payment
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
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
class Payment {

	const METHOD_NAME = 'shipping_nova_poshta_for_woocommerce_cod';
	/**
	 * Require shipping methods
	 *
	 * @codeCoverageIgnore
	 */
	public function require_methods() {
		require_once plugin_dir_path( __DIR__ ) . 'payment/class-nova-poshta-gateway-cod.php';
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'require_methods' ] );

		add_filter( 'woocommerce_payment_gateways', [ $this, 'register_methods' ] );
	}

	/**
	 * Register shipping method
	 *
	 * @param array $methods Shipping methods.
	 *
	 * @return array
	 */
	public function register_methods( array $methods ): array {
		$methods[ self::METHOD_NAME ] = 'Nova_Poshta_Gateway_COD';

		return $methods;
	}
}

