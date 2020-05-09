<?php
/**
 * Admin area tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Exception;
use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;
use WP_Mock\Functions;

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
		WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $admin, 'enqueue_styles' ] );
		WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $admin, 'enqueue_scripts' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $admin, 'add_menu' ] );
		WP_Mock::expectActionAdded( 'admin_init', [ $admin, 'register_setting' ] );
		WP_Mock::expectFilterAdded(
			'pre_update_option_shipping-nova-poshta-for-woocommerce',
			[
				$admin,
				'validate',
			],
			10,
			2
		);

		$admin->hooks();
	}

	/**
	 * Test styles
	 */
	public function test_do_NOT_enqueue_styles() {
		global $current_screen;

		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen           = new stdClass();
		$current_screen->base     = 'something';
		$current_screen->taxonomy = 'something';

		$admin = $this->instance();

		$admin->enqueue_styles();
	}

	/**
	 * Test styles
	 */
	public function test_enqueue_styles() {
		global $current_screen;

		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		WP_Mock::userFunction( 'plugin_dir_url' )->
		times( 4 );
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( 'np-select2', Functions::type( 'string' ), [], Main::VERSION, 'all' )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( 'np-tip-tip', Functions::type( 'string' ), [], Main::VERSION, 'all' )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( Main::PLUGIN_SLUG, Functions::type( 'string' ), [ 'np-select2' ], Main::VERSION, 'all' )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_style' )->
		with( Main::PLUGIN_SLUG . '-front', Functions::type( 'string' ), [ 'np-select2' ], Main::VERSION, 'all' )->
		once();

		$admin = $this->instance();

		$admin->enqueue_styles();
	}

	/**
	 * Test scripts
	 */
	public function test_do_NOT_enqueue_scripts() {
		global $current_screen;

		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen           = new stdClass();
		$current_screen->base     = 'something';
		$current_screen->taxonomy = 'something';

		$admin = $this->instance();

		$admin->enqueue_scripts();
	}

	/**
	 * Test scripts
	 */
	public function test_enqueue_scripts() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		$locale               = 'uk';
		$admin_url            = '/admin-url/';
		$nonce                = 'nonce123';
		WP_Mock::userFunction( 'plugin_dir_url' )->
		times( 4 );
		WP_Mock::userFunction( 'admin_url' )->
		once()->
		andReturn( $admin_url );
		WP_Mock::userFunction( 'wp_create_nonce' )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with( 'np-select2', Functions::type( 'string' ), [ 'jquery' ], Main::VERSION, true )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with( 'np-tip-tip', Functions::type( 'string' ), [ 'jquery' ], Main::VERSION, true )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with( 'select2-i18n-uk', Functions::type( 'string' ), [ 'jquery', 'np-select2' ], Main::VERSION, true )->
		once();
		WP_Mock::userFunction( 'wp_enqueue_script' )->
		with( Main::PLUGIN_SLUG, Functions::type( 'string' ), [ 'jquery', 'np-select2' ], Main::VERSION, true )->
		once();
		WP_Mock::userFunction(
			'wp_localize_script',
			[
				'args' => [
					Main::PLUGIN_SLUG,
					'shipping_nova_poshta_for_woocommerce',
					[
						'url'      => $admin_url,
						'nonce'    => $nonce,
						'language' => $locale,
					],
				],
			]
		);
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
		WP_Mock::userFunction( 'register_setting' )->
		with( Main::PLUGIN_SLUG, Main::PLUGIN_SLUG )->
		once();

		$admin = $this->instance();
		$admin->register_setting();
	}

	/**
	 * Test adding menu
	 */
	public function test_add_menu() {
		$admin = $this->instance();
		WP_Mock::passthruFunction( 'plugin_dir_url' )->
		once();
		WP_Mock::userFunction( 'add_menu_page' )->
		with(
			Main::PLUGIN_NAME,
			Main::PLUGIN_NAME,
			'manage_options',
			Main::PLUGIN_SLUG,
			[
				$admin,
				'page_options',
			],
			Functions::type( 'string' )
		)->
		once();

		$admin->add_menu();
	}

	/**
	 * Test page option tab general
	 */
	public function test_page_options_general() {
		WP_Mock::userFunction( 'plugin_dir_path' )->
		twice();
		WP_Mock::userFunction( 'get_admin_url' )->
		once();
		WP_Mock::userFunction( 'checked' )->
		once();
		WP_Mock::userFunction( 'settings_errors' )->
		with( Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'settings_fields' )->
		with( Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'submit_button', [ 'times' => 1 ] );
		$city_id      = 'city-id';
		$warehouse_id = 'warehouse-id';
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( 'City name' );
		$api
			->shouldReceive( 'warehouses' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( [ 'Warehuse #1' ] );
		WP_Mock::userFunction( 'selected' )->once();
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
		WP_Mock::userFunction( 'wp_kses_post' )->
		with( 'If you do not have an API key, then you can get it in the <a href="https://new.novaposhta.ua/#/1/settings/developers" target="_blank">personal account of Nova Poshta</a>. Unfortunately, without the API key, the plugin will not work :(' )->
		once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );

		ob_start();
		$admin->page_options();

		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test creating invoice
	 *
	 * @dataProvider dp_request
	 *
	 * @param array $request Request example.
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_page_create_invoice( array $request ) {
		$_POST[ Main::PLUGIN_SLUG ] = $request;
		WP_Mock::userFunction( 'check_admin_referer' )->
		with( Main::PLUGIN_SLUG . '-invoice', Main::PLUGIN_SLUG . '_nonce' )->
		once()->
		andReturn( false );
		$request_to_api = array_values( $request );
		array_push( $request_to_api, 1 );
		array_push( $request_to_api, 0 );
		FunctionMocker::replace( 'filter_input', $request );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->between( 0, 1 )
			->withArgs( $request_to_api );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'plugin_dir_path' )->once();
		WP_Mock::userFunction( 'get_admin_url' )->once();
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );
		ob_start();

		$admin->page_options();

		$this->assertnotEmpty( ob_get_clean() );
	}

	/**
	 * Create invoice request
	 * Data provider for test_controller().
	 *
	 * @return array
	 */
	public function dp_request() {
		return [
			[
				[
					'first_name'   => 'First name',
					'last_name'    => 'Last name',
					'phone'        => '123456',
					'city_id'      => 'city-id',
					'warehouse_id' => 'warehouse-id',
					'price'        => 777,
				],
			],
		];
	}

	/**
	 * Test page option tab create invoice
	 */
	public function test_page_options_create_invoice() {
		$user_id = 10;
		$locale  = 'uk';
		WP_Mock::userFunction( 'plugin_dir_path' )->twice();
		WP_Mock::userFunction( 'get_admin_url' )->once();
		WP_Mock::userFunction( 'submit_button' )->once();
		WP_Mock::userFunction( 'get_current_user_id' )->
		once()->
		andReturn( $user_id );
		WP_Mock::userFunction( 'wp_nonce_field' )->
		with( Main::PLUGIN_SLUG . '-invoice', Main::PLUGIN_SLUG . '_nonce', false )->
		once();
		WP_Mock::onFilter( 'shipping_nova_poshta_for_woocommerce_default_city' )->
		with( '', $user_id, $locale )->
		reply( 'city' );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->andReturn( [ 'city-id' => 'city-info' ] );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->andReturn( [ 'warehouse-id' => 'warehouse-info' ] );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'get_current_language' )
			->once()
			->andReturn( $locale );
		$admin = new Admin( $api, $settings, $language );
		FunctionMocker::replace( 'filter_input', 'internet_document' );
		ob_start();
		$admin->page_options();

		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test validation API key and show notice
	 */
	public function test_validate() {
		$key = 'some-key';
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'validate' )
			->once()
			->withArgs( [ $key ] );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'add_settings_error' )->
		with( Main::PLUGIN_SLUG, 403, Functions::type( 'string' ) )->
		once();
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
			->withArgs( [ $key ] )
			->andReturn( true );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$language = Mockery::mock( 'Nova_Poshta\Core\Language' );
		$admin    = new Admin( $api, $settings, $language );

		$admin->validate( [ 'api_key' => $key ] );
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

}
