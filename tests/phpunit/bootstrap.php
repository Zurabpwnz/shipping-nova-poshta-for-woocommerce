<?php
/**
 * Bootstrap file for Woo Nova Poshta phpunit tests.
 *
 * @package woo-nova-poshta
 */

use tad\FunctionMocker\FunctionMocker;

WP_Mock::bootstrap();

$plugin_path = __DIR__ . '/../../';

FunctionMocker::init(
	[
		'whitelist'             => [
			realpath( $plugin_path . 'admin/' ),
			realpath( $plugin_path . 'core/' ),
			realpath( $plugin_path . 'core/' ),
		],
		'blacklist'             => [
			realpath( $plugin_path ),
		],
		'redefinable-internals' => [ 'filter_input' ],
	]
);
