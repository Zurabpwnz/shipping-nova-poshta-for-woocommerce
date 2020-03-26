<?php
/**
 * Plugin Name: Woo Nova Poshta
 * Plugin URI:  https://github.com/mdenisenko/woo-nova-poshta
 * Description: Способ доставки Нова Пошта и генерация накладних Нова Пошта.
 * Version: 1.0.0
 * Author: Maksym Denysenko
 * Text Domain: woo-nova-poshta
 *
 * @package Nova Poshta
 */

use Nova_Poshta\Core\Main;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

$main = new Main();
$main->init();

function krya() {
	echo 'krya-activate';
}
register_activation_hook( __FILE__, 'krya' );
