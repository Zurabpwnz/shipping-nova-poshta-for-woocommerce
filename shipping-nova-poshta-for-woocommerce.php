<?php
/**
 * Shipping Nova Poshta for WooCommerce
 *
 * Plugin Name: Shipping Nova Poshta for WooCommerce
 * Plugin URI:  https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * Description: Shipping method for Nova Poshta. Creating internet documents for orders.
 * Version: 1.2.1
 * Author: WP Punk, Anton Serednii
 * Author URI: https://profiles.wordpress.org/wppunk/
 * License: GPLv2 or later
 * Text Domain: shipping-nova-poshta-for-woocommerce
 *
 * @package Shipping Nova Poshta for WooCommerce
 * @author  WP Punk, Anton Serednii
 */

use Nova_Poshta\Core\Main;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$main = new Main();
$main->init();
