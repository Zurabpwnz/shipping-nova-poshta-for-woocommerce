<?php
/**
 * Bootstrap class
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Admin\Admin;
use Nova_Poshta\Admin\Notice;
use Nova_Poshta\Admin\User;
use Nova_Poshta\Front\Front;

/**
 * Class Main
 *
 * @package Nova_Poshta\Core
 */
class Main {

	/**
	 * Plugin name
	 */
	const PLUGIN_NAME = 'Nova Poshta';
	/**
	 * Plugin slug
	 */
	const PLUGIN_SLUG = 'shipping-nova-poshta-for-woocommerce';
	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0';
	/**
	 * Plugin settings
	 *
	 * @var Settings
	 */
	private $settings;
	/**
	 * Plugin API
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Init plugin hooks
	 */
	public function init() {

		$notice = new Notice();
		$notice->hooks();
		if ( ! $this->is_woocommerce_active() ) {
			$notice->add(
				'error',
				sprintf(
				/* translators: 1: Plugin name */
					__(
						'<strong>%s</strong> extends WooCommerce functionality and does not work without it.',
						'shipping-nova-poshta-for-woocommerce'
					),
					self::PLUGIN_NAME
				)
			);

			return;
		}

		$this->settings = new Settings( $notice );
		$shipping       = new Shipping( $notice );

		$shipping->hooks();
		$this->define_hooks_without_api_key();

		if ( $this->settings->api_key() ) {
			$this->define_hooks_with_api_key();
		}
	}

	/**
	 * Is WooCommerce active
	 *
	 * @return bool
	 */
	private function is_woocommerce_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			// @codeCoverageIgnoreStart
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			// @codeCoverageIgnoreEnd
		}

		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Define hooks without API key
	 */
	private function define_hooks_without_api_key() {
		$language = new Language();
		$db       = new DB( $language );
		$db->hooks();

		$this->api = new API( $db, $this->settings );

		$admin = new Admin( $this->api, $this->settings );
		$admin->hooks();
	}

	/**
	 * Define hooks with API key
	 */
	private function define_hooks_with_api_key() {
		$ajax = new AJAX( $this->api );
		$ajax->hooks();

		$checkout = new Checkout();
		$checkout->hooks();

		$front = new Front();
		$front->hooks();

		$order = new Order( $this->api );
		$order->hooks();

		$thank_you = new Thank_You( $this->api );
		$thank_you->hooks();

		$user = new User( $this->api );
		$user->hooks();
	}

}
