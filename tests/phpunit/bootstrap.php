<?php
/**
 * Bootstrap file for Woo Nova Poshta phpunit tests.
 *
 * @package shipping-nova-poshta-for-woocommerce
 */

use tad\FunctionMocker\FunctionMocker;

$plugin_path = __DIR__ . '/../../';

require_once $plugin_path . 'vendor/autoload.php';

FunctionMocker::init(
	[
		'whitelist'             => [
			realpath( $plugin_path . 'admin/' ),
			realpath( $plugin_path . 'core/' ),
			realpath( $plugin_path . 'front/' ),
			realpath( $plugin_path . 'shipping/' ),
		],
		'blacklist'             => [
			realpath( $plugin_path ),
		],
		'redefinable-internals' => [ 'class_exists', 'filter_input', 'filter_var', 'is_a', 'constant' ],
	]
);

WP_Mock::bootstrap();
