<?php
/**
 * Thank you page tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Front;

use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Thank_You
 *
 * @package Nova_Poshta\Core
 */
class Test_Front extends Test_Case {

	/**
	 * Test styles on not checkout page
	 */
	public function test_styles_on_no_checkout_page() {
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( false )->
		once();
		$front = new Front();

		$front->styles();
	}

	/**
	 * Test styles on checkout page
	 */
	public function test_enqueue_styles() {
		$plugin_url = 'https://site.com/wp-content/plugins/woo-nova-poshta/';
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( true )->
		once();
		WP_Mock::userFunction( 'plugin_dir_url' )->
		twice()->
		andReturn( $plugin_url );
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		withArgs( [ 'select2', $plugin_url . 'assets/css/select2.min.css', [], Main::VERSION, 'all' ] )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		withArgs( [ Main::PLUGIN_SLUG, $plugin_url . 'assets/css/main.css', [ 'select2' ], Main::VERSION, 'all' ] )->
		once();
		$front = new Front();

		$front->styles();
	}

	/**
	 * Test scripts on not checkout page
	 */
	public function test_scripts_on_no_checkout_page() {
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( false )->
		once();
		$front = new Front();

		$front->scripts();
	}

	/**
	 * Test scripts on checkout page
	 */
	public function test_enqueue_scripts() {
		$plugin_url = 'https://site.com/wp-content/plugins/woo-nova-poshta/';
		$admin_ajax = 'https://site.com/admin-ajax.php';
		$nonce      = 'nonce';
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( true )->
		once();
		WP_Mock::userFunction( 'plugin_dir_url' )->
		twice()->
		andReturn( $plugin_url );
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		withArgs( [ 'select2', $plugin_url . 'assets/js/select2.min.js', [ 'jquery' ], Main::VERSION, true ] )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		withArgs(
			[
				Main::PLUGIN_SLUG,
				$plugin_url . 'assets/js/main.js',
				[ 'jquery', 'select2' ],
				Main::VERSION,
				true,
			]
		)->
		once();
		WP_Mock::userFunction( 'admin_url' )->
		withArgs( [ 'admin-ajax.php' ] )->
		once()->
		andReturn( $admin_ajax );
		WP_Mock::userFunction( 'wp_create_nonce' )->
		withArgs( [ Main::PLUGIN_SLUG ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_localize_script' )->
		withArgs(
			[
				Main::PLUGIN_SLUG,
				'woo_nova_poshta',
				[
					'url'   => $admin_ajax,
					'nonce' => $nonce,
				],
			]
		)->
		once();

		$front = new Front();

		$front->scripts();
	}

}
