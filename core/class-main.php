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

		$db   = new DB();
		$api  = new API( $db );
		$ajax = new AJAX( $api );
		add_action( 'wp_ajax_woo_nova_poshta_city', [ $ajax, 'cities' ] );
		add_action( 'wp_ajax_nopriv_woo_nova_poshta_city', [ $ajax, 'cities' ] );
		add_action( 'wp_ajax_woo_nova_poshta_warehouse', [ $ajax, 'warehouses' ] );
		add_action( 'wp_ajax_nopriv_woo_nova_poshta_warehouse', [ $ajax, 'warehouses' ] );

		$admin = new Admin( $api );
		add_action( 'admin_enqueue_scripts', [ $admin, 'styles' ] );
		add_action( 'admin_enqueue_scripts', [ $admin, 'scripts' ] );
		add_action( 'admin_menu', [ $admin, 'add_menu' ] );
		add_action( 'admin_init', [ $admin, 'register_setting' ] );

		$shipping = new Shipping();
		add_action( 'woocommerce_shipping_init', [ $shipping, 'require_methods' ] );
		add_filter( 'woocommerce_shipping_methods', [ $shipping, 'register_methods' ] );

		$checkout = new Checkout();
		add_action( 'woocommerce_after_shipping_rate', [ $checkout, 'fields' ] );
		add_action( 'woocommerce_checkout_process', [ $checkout, 'validate' ] );

		$front = new Front();
		add_action( 'wp_enqueue_scripts', [ $front, 'styles' ] );
		add_action( 'wp_enqueue_scripts', [ $front, 'scripts' ] );

		$order = new Order( $api );
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $order, 'save' ], 10, 4 );
		add_filter( 'woocommerce_order_item_display_meta_key', [ $order, 'labels' ], 10, 2 );
		add_filter( 'woocommerce_order_item_display_meta_value', [ $order, 'values' ], 10, 2 );

		$thank_you = new Thank_You( $api );
		add_filter( 'woocommerce_get_order_item_totals', [ $thank_you, 'shipping' ], 10, 2 );

		$user = new User( $api );
		add_action( 'woo_nova_poshta_user_fields', [ $user, 'fields' ] );
		add_filter( 'woo_nova_poshta_default_city_id', [ $user, 'city' ] );
		add_filter( 'woo_nova_poshta_default_warehouse_id', [ $user, 'warehouse' ] );
		add_action( 'woocommerce_checkout_create_order_shipping_item', [ $user, 'checkout' ], 10, 4 );
	}

}
