<?php
/**
 * Shipping Nova Poshta for WooCommerce
 *
 * Plugin Name: Shipping Nova Poshta for WooCommerce
 * Plugin URI:  https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * Description: Shipping method for Nova Poshta. Creating internet documents for orders.
 * Version: 1.0.0
 * Author: Maksym Denysenko, Serednii Anton
 * Author URI: https://profiles.wordpress.org/wppunk/
 *
 * Text Domain: shipping-nova-poshta-for-woocommerce
 * Domain Path: /languages/
 *
 * @package Shipping Nova Poshta for WooCommerce
 * @author  Maksym Denysenko, Serednii Anton
 */

use Nova_Poshta\Core\Main;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$main = new Main();
$main->init();
