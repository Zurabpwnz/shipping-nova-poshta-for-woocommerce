<?php
/**
 * Bootstrap class
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/woo-nova-poshta
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
	const PLUGIN_SLUG = 'woo-nova-poshta';
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

		$this->settings = new Settings();

		$this->define_hooks_without_api_key();

		if ( $this->settings->api_key() ) {
			$this->define_hooks_with_api_key();
		}
	}

	/**
	 * Define hooks without API key
	 */
	private function define_hooks_without_api_key() {
		$db = new DB();
		$db->hooks();

		$this->api = new API( $db, $this->settings );

		$admin = new Admin( $this->api, $this->settings );
		$admin->hooks();

		$shipping = new Shipping();
		$shipping->hooks();

		$notice = new Notice( $this->settings, $shipping );
		$notice->hooks();

		$language = new Language();
		$language->hooks();
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
