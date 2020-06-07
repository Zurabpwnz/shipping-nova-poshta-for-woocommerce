<?php
/**
 * Front tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Front;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;
use function Brain\Monkey\Functions\expect;

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

		$front->hooks();

		$this->assertTrue( has_action( 'wp_enqueue_scripts', [ $front, 'enqueue_styles' ] ) );
		$this->assertTrue( has_action( 'wp_enqueue_scripts', [ $front, 'enqueue_scripts' ] ) );
	}

	/**
	 * Test styles on not checkout page
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_styles_on_no_checkout_page() {
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_styles();
	}

	/**
	 * Test styles on checkout page
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_styles() {
		$plugin_url = 'https://site.com/wp-content/plugins/shipping-nova-poshta-for-woocommerce/';
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->withAnyArgs()
			->twice()
			->andReturn( $plugin_url );
		expect( 'wp_enqueue_style' )
			->with( 'np-select2', $plugin_url . 'assets/css/select2.min.css', [], Main::VERSION, 'all' )
			->once();
		expect( 'wp_enqueue_style' )
			->with( Main::PLUGIN_SLUG, $plugin_url . 'assets/css/main.css', [ 'np-select2' ], Main::VERSION, 'all' )
			->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_styles();
	}

	/**
	 * Test scripts on not checkout page
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_scripts_on_no_checkout_page() {
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$front    = new Front( $language );

		$front->enqueue_scripts();
	}

	/**
	 * Test scripts on checkout page
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_scripts() {
		$locale     = 'ua';
		$plugin_url = 'https://site.com/wp-content/plugins/shipping-nova-poshta-for-woocommerce/';
		$admin_ajax = 'https://site.com/admin-ajax.php';
		$nonce      = 'nonce';
		expect( 'is_checkout' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->withAnyArgs()
			->times( 3 )
			->andReturn( $plugin_url );
		expect( 'wp_enqueue_script' )
			->with(
				'np-select2',
				$plugin_url . 'assets/js/select2.min.js',
				[ 'jquery' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				'select2-i18n-' . $locale,
				$plugin_url . 'assets/js/i18n/' . $locale . '.js',
				[ 'jquery', 'np-select2' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				Main::PLUGIN_SLUG,
				$plugin_url . 'assets/js/main.js',
				[ 'jquery', 'np-select2' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'admin_url' )
			->with( 'admin-ajax.php' )
			->once()
			->andReturn( $admin_ajax );
		expect( 'wp_create_nonce' )
			->with( Main::PLUGIN_SLUG )
			->once()
			->andReturn( $nonce );
		expect( 'wp_localize_script' )
			->with(
				Main::PLUGIN_SLUG,
				'shipping_nova_poshta_for_woocommerce',
				[
					'url'      => $admin_ajax,
					'nonce'    => $nonce,
					'language' => $locale,
				]
			)
			->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->times( 3 )
			->andReturn( $locale );
		$front = new Front( $language );

		$front->enqueue_scripts();
	}

}
