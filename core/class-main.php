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
		$api      = new API( $db, $settings );
		$ajax     = new AJAX( $api );
		$ajax->hooks();

		$admin = new Admin( $api, $settings );
		add_action( 'admin_enqueue_scripts', [ $admin, 'styles' ] );
		add_action( 'admin_enqueue_scripts', [ $admin, 'scripts' ] );
		add_action( 'admin_menu', [ $admin, 'add_menu' ] );
		add_action( 'admin_init', [ $admin, 'register_setting' ] );
		add_filter( 'pre_update_option_woo-nova-poshta', [ $admin, 'validate' ], 10, 2 );

		$shipping = new Shipping();
		add_filter( 'woocommerce_shipping_methods', [ $shipping, 'register_methods' ] );
		add_action( 'woocommerce_shipping_init', [ $shipping, 'require_methods' ] );

		$notice = new Notice( $settings, $shipping );
		add_action( 'admin_notices', [ $notice, 'notices' ] );

		$checkout = new Checkout();
		add_action( 'woocommerce_after_shipping_rate', [ $checkout, 'fields' ] );
		add_action( 'woocommerce_checkout_process', [ $checkout, 'validate' ] );

		$front = new Front();
		add_action( 'wp_enqueue_scripts', [ $front, 'styles' ] );
		add_action( 'wp_enqueue_scripts', [ $front, 'scripts' ] );

		$order = new Order( $api );
		$order->hooks();

		$thank_you = new Thank_You( $api );
		add_filter( 'woocommerce_get_order_item_totals', [ $thank_you, 'shipping' ], 10, 2 );

		$user = new User( $api );
		add_action( 'woo_nova_poshta_user_fields', [ $user, 'fields' ] );
		add_filter( 'woo_nova_poshta_default_city_id', [ $user, 'city' ] );
		add_filter( 'woo_nova_poshta_default_warehouse_id', [ $user, 'warehouse' ] );
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $user, 'checkout' ], 10, 4 );

		$language = new Language();
		add_action( 'plugins_loaded', [ $language, 'load' ] );

		register_activation_hook(
			plugin_dir_path( __DIR__ ) . dirname( plugin_basename( __DIR__ ) ) . '.php',
			[ $db, 'create' ]
		);
	}

}
