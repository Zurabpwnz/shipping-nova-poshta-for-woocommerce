<?php
/**
 * Front tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Front;

use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Front
 *
 * @package Nova_Poshta\Core
 */
class Test_Front extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		WP_Mock::expectActionAdded( 'wp_enqueue_scripts', [ $front, 'enqueue_styles' ] );
		WP_Mock::expectActionAdded( 'wp_enqueue_scripts', [ $front, 'enqueue_scripts' ] );

		$front->hooks();
	}

	/**
	 * Test styles on not checkout page
	 */
	public function test_styles_on_no_checkout_page() {
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( false )->
		once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_styles();
	}

	/**
	 * Test styles on checkout page
	 */
	public function test_enqueue_styles() {
		$plugin_url = 'https://site.com/wp-content/plugins/shipping-nova-poshta-for-woocommerce/';
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( true )->
		once();
		WP_Mock::userFunction( 'plugin_dir_url' )->
		twice()->
		andReturn( $plugin_url );
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( 'select2', $plugin_url . 'assets/css/select2.min.css', [], Main::VERSION, 'all' )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( Main::PLUGIN_SLUG, $plugin_url . 'assets/css/main.css', [ 'select2' ], Main::VERSION, 'all' )->
		once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_styles();
	}

	/**
	 * Test scripts on not checkout page
	 */
	public function test_scripts_on_no_checkout_page() {
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( false )->
		once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_scripts();
	}

	/**
	 * Test scripts on checkout page
	 */
	public function test_enqueue_scripts() {
		$locale     = 'ua';
		$plugin_url = 'https://site.com/wp-content/plugins/shipping-nova-poshta-for-woocommerce/';
		$admin_ajax = 'https://site.com/admin-ajax.php';
		$nonce      = 'nonce';
		WP_Mock::userFunction( 'is_checkout' )->
		andReturn( true )->
		once();
		WP_Mock::userFunction( 'plugin_dir_url' )->
		times( 3 )->
		andReturn( $plugin_url );
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with(
			'select2',
			$plugin_url . 'assets/js/select2.min.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		)->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with(
			'select2-i18n-' . $locale,
			$plugin_url . 'assets/js/i18n/' . $locale . '.js',
			[ 'jquery', 'select2' ],
			Main::VERSION,
			true
		)->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with(
			Main::PLUGIN_SLUG,
			$plugin_url . 'assets/js/main.js',
			[ 'jquery', 'select2' ],
			Main::VERSION,
			true
		)->
		once();
		WP_Mock::userFunction( 'admin_url' )->
		with( 'admin-ajax.php' )->
		once()->
		andReturn( $admin_ajax );
		WP_Mock::userFunction( 'wp_create_nonce' )->
		with( Main::PLUGIN_SLUG )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_localize_script' )->
		with(
			Main::PLUGIN_SLUG,
			'shipping_nova_poshta_for_woocommerce',
			[
				'url'      => $admin_ajax,
				'nonce'    => $nonce,
				'language' => $locale,
			]
		)->
		once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->times( 3 )
			->andReturn( $locale );
		$front = new Front( $language );

		$front->enqueue_scripts();
	}

}
