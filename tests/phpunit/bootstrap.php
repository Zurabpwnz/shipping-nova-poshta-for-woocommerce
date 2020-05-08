<?php
/**
 * Bootstrap file for Woo Nova Poshta phpunit tests.
 *
 * @package shipping-nova-poshta-for-woocommerce
 */

use Composer\Autoload\ClassMapGenerator;
use tad\FunctionMocker\FunctionMocker;

$plugin_path = __DIR__ . '/../../';

require_once $plugin_path . 'vendor/autoload.php';

try {
	if ( ! spl_autoload_register( 'autoload_tests_classes' ) ) {
		echo 'Test classes cannot be loaded!';
		exit( 1 );
	}
} catch ( Exception $e ) {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $e->getMessage();
	exit( 1 );
}

function autoload_tests_classes( $class ) {
	static $maps;

	if ( ! $maps ) {
		$dirs = [
			__DIR__ . '/stubs',
		];

		$maps = [];
		foreach ( $dirs as $dir ) {
			$maps = array_merge( $maps, ClassMapGenerator::createMap( $dir ) );
		}
	}

	if ( $maps && array_key_exists( $class, $maps ) ) {
		// @noinspection PhpIncludeInspection
		require_once $maps[ $class ];
	}
}

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
