<?php
/**
 * Admin area tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
use stdClass;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Filters\expectApplied;

/**
 * Class Test_Admin
 *
 * @package Nova_Poshta\Admin
 */
class Test_Admin extends Test_Case {

	/**
	 * Tear down the test.
	 */
	public function tearDown() {
		//phpcs:ignore PEAR.Functions.FunctionCallSignature.SpaceBeforeOpenBracket
		unset ( $GLOBALS['current_screen'] );
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST );

		parent::tearDown();
	}

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$admin = $this->instance();

		$admin->hooks();

		$this->assertTrue( has_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_styles' ] ) );
		$this->assertTrue( has_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_scripts' ] ) );
		$this->assertTrue( has_action( 'admin_menu', [ $admin, 'add_menu' ] ) );
		$this->assertTrue( has_action( 'admin_init', [ $admin, 'register_setting' ] ) );
		$this->assertTrue(
			has_filter(
				'pre_update_option_shipping-nova-poshta-for-woocommerce',
				[ $admin, 'validate' ]
			)
		);
	}

	/**
	 * Get testing object
	 *
	 * @return Admin
	 */
	private function instance(): Admin {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );

		return new Admin( $api, $settings, $language );
	}

	/**
	 * Test don't enqueue styles
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_do_NOT_enqueue_styles() {
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$admin = $this->instance();

		$admin->enqueue_styles();
	}

	/**
	 * Test enqueue styles for admin
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_admin_styles() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen           = new stdClass();
		$current_screen->base     = 'something';
		$current_screen->taxonomy = 'something';
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->with(
				Mockery::anyOf(
					__DIR__ . '/../../../../admin/class-admin.php',
					__DIR__ . '/../../../../admin'
				)
			)
			->once()
			->andReturn( '/some/path' );
		expect( 'wp_enqueue_style' )
			->with(
				'np-notice',
				'/some/path/assets/css/notice.css',
				[],
				Main::VERSION,
				'all'
			)
			->once();
		$admin = $this->instance();

		$admin->enqueue_styles();
	}

	/**
	 * Test styles
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_styles() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->with(
				Mockery::anyOf(
					__DIR__ . '/../../../../admin/class-admin.php',
					__DIR__ . '/../../../../admin'
				)
			)
			->times( 5 )
			->andReturn( '/some/path' );
		expect( 'wp_enqueue_style' )
			->with(
				'np-notice',
				'/some/path/assets/css/notice.css',
				[],
				Main::VERSION,
				'all'
			)
			->once();
		expect( 'wp_enqueue_style' )
			->with(
				'np-select2',
				'/some/path/front/assets/css/select2.min.css',
				[],
				Main::VERSION,
				'all'
			)
			->once();
		expect( 'wp_enqueue_style' )
			->with(
				'np-tip-tip',
				'/some/path/assets/css/tip-tip.css',
				[],
				Main::VERSION,
				'all'
			)
			->once();
		expect( 'wp_enqueue_style' )
			->with(
				Main::PLUGIN_SLUG,
				'/some/path/assets/css/main.css',
				[ 'np-select2' ],
				Main::VERSION,
				'all'
			)
			->once();
		expect( 'wp_enqueue_style' )
			->with(
				Main::PLUGIN_SLUG . '-front',
				'/some/path/front/assets/css/main.css',
				[ 'np-select2' ],
				Main::VERSION,
				'all'
			)
			->once();

		$admin = $this->instance();

		$admin->enqueue_styles();
	}

	/**
	 * Test don't enqueue scripts
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_do_NOT_enqueue_scripts() {
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( false );
		$admin = $this->instance();

		$admin->enqueue_scripts();
	}

	/**
	 * Test enqueue styles for admin
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_admin_scripts() {
		$locale    = 'uk';
		$admin_url = '/admin-url/';
		$nonce     = 'nonce123';
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen           = new stdClass();
		$current_screen->base     = 'something';
		$current_screen->taxonomy = 'something';
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->with(
				Mockery::anyOf(
					__DIR__ . '/../../../../admin/class-admin.php',
					__DIR__ . '/../../../../admin'
				)
			)
			->once()
			->andReturn( '/some/path' );
		expect( 'wp_enqueue_script' )
			->with(
				'np-notice',
				'/some/path/front/assets/js/np-notice.js',
				[ 'jquery' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'admin_url' )
			->with( 'admin-ajax.php' )
			->once()
			->andReturn( $admin_url );
		expect( 'wp_create_nonce' )
			->with( Main::PLUGIN_SLUG )
			->once()
			->andReturn( $nonce );
		expect( 'wp_localize_script' )
			->with(
				'np-notice',
				'shipping_nova_poshta_for_woocommerce',
				[
					'url'      => $admin_url,
					'nonce'    => $nonce,
					'language' => $locale,
				]
			)
			->once();
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->once()
			->andReturn( $locale );

		$admin = new Admin( $api, $settings, $language );

		$admin->enqueue_scripts();
	}

	/**
	 * Test scripts
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_enqueue_scripts() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		$locale               = 'uk';
		$admin_url            = '/admin-url/';
		$nonce                = 'nonce123';
		expect( 'is_admin' )
			->withNoArgs()
			->once()
			->andReturn( true );
		expect( 'plugin_dir_url' )
			->with(
				Mockery::anyOf(
					__DIR__ . '/../../../../admin/class-admin.php',
					__DIR__ . '/../../../../admin'
				)
			)
			->times( 5 )
			->andReturn( '/some/path' );
		expect( 'wp_enqueue_script' )
			->with(
				'np-notice',
				'/some/path/front/assets/js/np-notice.js',
				[ 'jquery' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'admin_url' )
			->with( 'admin-ajax.php' )
			->once()
			->andReturn( $admin_url );
		expect( 'wp_create_nonce' )
			->with( Main::PLUGIN_SLUG )
			->once()
			->andReturn( $nonce );
		expect( 'wp_localize_script' )
			->with(
				'np-notice',
				'shipping_nova_poshta_for_woocommerce',
				[
					'url'      => $admin_url,
					'nonce'    => $nonce,
					'language' => $locale,
				]
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				'np-select2',
				'/some/path/front/assets/js/select2.min.js',
				[ 'jquery' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				'select2-i18n-uk',
				'/some/path/front/assets/js/i18n/uk.js',
				[ 'jquery', 'np-select2' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				'np-tip-tip',
				'/some/path/assets/js/jquery.tip-tip.min.js',
				[ 'jquery' ],
				Main::VERSION,
				true
			)
			->once();
		expect( 'wp_enqueue_script' )
			->with(
				Main::PLUGIN_SLUG,
				'/some/path/assets/js/main.js',
				[ 'jquery', 'np-select2' ],
				Main::VERSION,
				true
			)
			->once();

		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->times( 3 )
			->andReturn( $locale );

		$admin = new Admin( $api, $settings, $language );

		$admin->enqueue_scripts();
	}

	/**
	 * Test register settings
	 */
	public function test_register_settings() {
		expect( 'register_setting' )
			->with( Main::PLUGIN_SLUG, Main::PLUGIN_SLUG )
			->once();

		$admin = $this->instance();
		$admin->register_setting();
	}

	/**
	 * Test adding menu
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_add_menu() {
		$admin = $this->instance();
		expect( 'plugin_dir_url' )
			->with( __DIR__ . '/../../../../admin' )
			->once()
			->andReturn( '/some/path/' );
		expect( 'add_menu_page' )
			->with(
				Main::PLUGIN_NAME,
				Main::PLUGIN_NAME,
				'manage_options',
				Main::PLUGIN_SLUG,
				[
					$admin,
					'page_options',
				],
				'/some/path/assets/img/nova-poshta.svg'
			)->
			once();

		$admin->add_menu();
	}

	/**
	 * Test page option tab general
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_page_options_general() {
		expect( 'plugin_dir_path' )
			->with()
			->twice();
		expect( 'get_admin_url' )
			->with( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
			->once();
		stubs(
			[
				'__',
				'esc_url',
				'esc_attr',
				'esc_attr_e',
				'selected',
				'checked',
			]
		);
		expect( 'settings_errors' )
			->with( Main::PLUGIN_SLUG )
			->once();
		expect( 'settings_fields' )
			->with( Main::PLUGIN_SLUG )
			->once();
		expect( 'submit_button' )
			->withNoArgs()
			->once();
		expect( 'wp_kses_post' )
			->with( 'If you do not have an API key, then you can get it in the <a href="https://new.novaposhta.ua/#/1/settings/developers" target="_blank">personal account of Nova Poshta</a>. Unfortunately, without the API key, the plugin will not work :(' )
			->once();
		$city_id      = 'city-id';
		$warehouse_id = 'warehouse-id';
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->with( $city_id )
			->once()
			->andReturn( 'City name' );
		$api
			->shouldReceive( 'warehouses' )
			->with( $city_id )
			->once()
			->andReturn( [ 'Warehuse #1' ] );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( 'api-key' );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $warehouse_id );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->twice()
			->andReturn( true );
		$settings
			->shouldReceive(
				'phone',
				'description',
				'default_weight_formula',
				'default_width_formula',
				'default_height_formula',
				'default_length_formula'
			)
			->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );

		ob_start();
		$admin->page_options();

		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test page option tab general first sign in
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_page_options_general_first_sign_in() {
		$city_id      = 'city-id';
		$default_city = 'City Name';
		$city_name    = $default_city;
		$warehouse_id = 'warehouse-id';
		$user_id      = 10;
		$locale       = 'uk';
		expect( 'plugin_dir_path' )
			->with()
			->twice();
		expect( 'get_admin_url' )
			->with( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
			->once();
		stubs(
			[
				'__',
				'esc_url',
				'esc_attr',
				'esc_attr_e',
				'selected',
				'checked',
			]
		);
		expect( 'settings_errors' )
			->with( Main::PLUGIN_SLUG )
			->once();
		expect( 'settings_fields' )
			->with( Main::PLUGIN_SLUG )
			->once();
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'submit_button' )
			->withNoArgs()
			->once();
		expect( 'wp_kses_post' )
			->with( 'If you do not have an API key, then you can get it in the <a href="https://new.novaposhta.ua/#/1/settings/developers" target="_blank">personal account of Nova Poshta</a>. Unfortunately, without the API key, the plugin will not work :(' )
			->once();
		expectApplied( 'shipping_nova_poshta_for_woocommerce_default_city' )
			->with( '', $user_id, $locale )
			->once()
			->andReturn( $default_city );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->with( '', 0 )
			->once()
			->andReturn(
				[
					'city_id_1' => 'City name 1',
					'city_id_2' => 'City name 2',
				]
			);
		$api
			->shouldReceive( 'cities' )
			->with( $default_city, 1 )
			->once()
			->andReturn( [ $city_id => $city_name ] );
		$api
			->shouldReceive( 'warehouses' )
			->with( $city_id )
			->once()
			->andReturn( [ 'Warehuse #1' ] );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->twice()
			->andReturn( 'api-key' );
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( '' );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $warehouse_id );
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->twice()
			->andReturn( true );
		$settings
			->shouldReceive(
				'phone',
				'description',
				'default_weight_formula',
				'default_width_formula',
				'default_height_formula',
				'default_length_formula'
			)
			->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->once()
			->andReturn( $locale );
		$admin = new Admin( $api, $settings, $language );

		ob_start();
		$admin->page_options();

		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test validation API key and show notice
	 */
	public function test_validate() {
		expect( 'add_settings_error' )
			->with( Main::PLUGIN_SLUG, 403, Mockery::type( 'string' ) )
			->once();
		when( '__' )
			->returnArg();
		$key = 'some-key';
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'validate' )
			->once()
			->with( $key );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );

		$admin->validate( [ 'api_key' => $key ] );
	}

	/**
	 * Test validation API key and show notice
	 */
	public function test_validate_and_update_cities() {
		$key = 'some-key';
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'validate' )
			->once()
			->with( $key )
			->andReturn( true );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );

		$admin->validate( [ 'api_key' => $key ] );
	}

}
