<?php
/**
 * Nova Poshta for WooCommerce
 *
 * Plugin Name: Nova Poshta for WooCommerce
 * Plugin URI:  https://github.com/wppunk/woo-nova-poshta
 * Description: Shipping method for Nova Poshta. Creating internet documents for orders.
 * Version: 1.0.0
 * Author: Maksym Denysenko
 * Author URI: https://profiles.wordpress.org/wppunk/
 *
 * Text Domain: woo-nova-poshta
 * Domain Path: /languages/
 *
 * @package Nova Poshta
 * @author  Maksym Denysenko
 */

use Nova_Poshta\Core\Main;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$main = new Main();
$main->init();
