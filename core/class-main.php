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
use Nova_Poshta\Core\Cache\Cache;
use Nova_Poshta\Core\Cache\Object_Cache;
use Nova_Poshta\Core\Cache\Transient_Cache;
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
	const VERSION = '1.2.1';
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
	 * Notice
	 *
	 * @var Notice
	 */
	private $notice;
	/**
	 * Language
	 *
	 * @var Language
	 */
	private $language;

	/**
	 * Init plugin hooks
	 */
	public function init() {
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
		$this->notice = new Notice();
		$this->notice->hooks();
		if ( ! $this->is_woocommerce_active() ) {
			$this->notice->add(
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
		}

		$object_cache = new Object_Cache();
		$object_cache->hooks();

		$transient_cache = new Transient_Cache();
		$transient_cache->hooks();

		$this->settings = new Settings( $this->notice );
		$shipping       = new Shipping( $this->notice, $object_cache );
		$shipping->hooks();

		$this->language = new Language();
		$this->language->hooks();

		$db = new DB( $this->language );
		$db->hooks();

		$this->api = new API( $db, $object_cache, $transient_cache, $this->settings );
		$this->api->hooks();

		$admin = new Admin( $this->api, $this->settings, $this->language );
		$admin->hooks();
	}

	/**
	 * Define hooks with API key
	 */
	private function define_hooks_with_api_key() {
		$calculator    = new Calculator();
		$shipping_cost = new Shipping_Cost( $this->api, $this->settings, $calculator );

		$ajax = new AJAX( $this->api, $shipping_cost );
		$ajax->hooks();

		$checkout = new Checkout();
		$checkout->hooks();

		$front = new Front( $this->language );
		$front->hooks();

		$order = new Order( $this->api );
		$order->hooks();

		$thank_you = new Thank_You( $this->api );
		$thank_you->hooks();

		$user = new User( $this->api, $this->language );
		$user->hooks();
	}

}
