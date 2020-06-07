<?php
/**
 * Bootstrap file for Woo Nova Poshta phpunit tests.
 *
 * @package shipping-nova-poshta-for-woocommerce
 */

use Composer\Autoload\ClassMapGenerator;

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

/**
 * Autoload stubs
 *
 * @param string $class Class name.
 */
function autoload_tests_classes( string $class ) {
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

define( 'PLUGIN_FILE', __DIR__ . '/../../shipping-nova-poshta-for-woocommerce.php' );
define( 'PLUGIN_DIR', dirname( PLUGIN_FILE ) );

define( 'ARRAY_A', 'ARRAY_A' );
