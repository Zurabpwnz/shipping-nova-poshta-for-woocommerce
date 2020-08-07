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

use Nova_Poshta\Admin\User;
use Nova_Poshta\Admin\Admin;
use Nova_Poshta\Front\Front;
use Nova_Poshta\Core\Cache\Cache;
use Nova_Poshta\Admin\Notice\Notice;
use Nova_Poshta\Admin\Product_Metabox;
use Nova_Poshta\Core\Cache\Object_Cache;
use Nova_Poshta\Core\Cache\Factory_Cache;
use Nova_Poshta\Admin\Admin_Manage_Orders;
use Nova_Poshta\Admin\Notice\Advertisement;
use Nova_Poshta\Core\Cache\Transient_Cache;
use Nova_Poshta\Admin\Product_Category_Metabox;

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
	const VERSION = '1.4.1';
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
	 * Transient cache
	 *
	 * @var Transient_Cache
	 */
	private $transient_cache;

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
		$object_cache = new Object_Cache();
		$object_cache->hooks();

		$this->transient_cache = new Transient_Cache();
		$this->transient_cache->hooks();

		$cache = new Factory_Cache( $this->transient_cache, $object_cache );

		$this->notice = new Notice( $this->transient_cache );
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

		$this->settings = new Settings( $this->notice );
		$shipping       = new Shipping( $this->notice, $cache );
		$shipping->hooks();

		$payment = new Payment();
		$payment->hooks();

		$this->language = new Language();
		$this->language->hooks();

		$db = new DB( $this->language );
		$db->hooks();

		$this->api = new API( $db, $cache, $this->settings );
		$this->api->hooks();

		$admin = new Admin( $this->api, $this->settings, $this->language );
		$admin->hooks();
	}

	/**
	 * Define hooks with API key
	 */
	private function define_hooks_with_api_key() {
		$calculator        = new Calculator();
		$shipping_cost     = new Shipping_Cost( $this->api, $this->settings, $calculator );
		$internet_document = new Internet_Document( $this->api, $shipping_cost, $this->notice );
		$admin             = new Admin_Manage_Orders( $internet_document );
		$admin->hooks();
		$advertisement = new Advertisement( $this->transient_cache );
		$advertisement->hooks();

		$ajax = new AJAX( $this->api, $shipping_cost );
		$ajax->hooks();

		$checkout = new Checkout();
		$checkout->hooks();

		$front = new Front( $this->language );
		$front->hooks();

		$order = new Order( $this->api, $shipping_cost, $internet_document );
		$order->hooks();

		$thank_you = new Thank_You( $this->api );
		$thank_you->hooks();

		$user = new User( $this->api, $this->language );
		$user->hooks();

		if ( $this->settings->is_shipping_cost_enable() ) {
			$product_cat_metabox = new Product_Category_Metabox();
			$product_cat_metabox->hooks();

			$product_metabox = new Product_Metabox();
			$product_metabox->hooks();
		}
	}

}
