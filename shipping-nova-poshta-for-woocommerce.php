<?php
/**
 * Shipping Nova Poshta for WooCommerce
 *
 * Plugin Name: Shipping Nova Poshta for WooCommerce
 * Plugin URI:  https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * Description: Select a branch on the checkout page. Creation of electronic invoices. Calculating shipping costs and
 * much more ...
 * Version: 1.3.1.1
 * Author: WP Punk, Anton Serednii
 * Author URI: https://profiles.wordpress.org/wppunk/
 * License: GPLv2 or later
 * Text Domain: shipping-nova-poshta-for-woocommerce
 *
 * @package Shipping Nova Poshta for WooCommerce
 * @author  WP Punk, Anton Serednii
 *
 * WC requires at least: 3.3
 * WC tested up to: 4.1.1
 */

use Nova_Poshta\Core\Main;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$main = new Main();
$main->init();
