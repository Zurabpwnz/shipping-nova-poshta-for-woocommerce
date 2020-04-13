<?php
/**
 * Bootstrap class
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
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
	 * Init plugin hooks
	 */
	public function init() {

		$settings = new Settings();
		$db       = new DB();
		$db->hooks();
		$api  = new API( $db, $settings );
		$ajax = new AJAX( $api );
		$ajax->hooks();

		$admin = new Admin( $api, $settings );
		$admin->hooks();

		$shipping = new Shipping();
		$shipping->hooks();

		$notice = new Notice( $settings, $shipping );
		$notice->hooks();

		$checkout = new Checkout();
		$checkout->hooks();

		$front = new Front();
		$front->hooks();

		$order = new Order( $api );
		$order->hooks();

		$thank_you = new Thank_You( $api );
		$thank_you->hooks();

		$user = new User( $api );
		$user->hooks();

		$language = new Language();
		$language->hooks();
	}

}
