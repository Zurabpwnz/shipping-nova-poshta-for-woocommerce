<?php
/**
 * Admin area tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Admin;

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
	 * Get testing object
	 *
	 * @return Admin
	 */
	private function instance(): Admin {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );

		return new Admin( $api, $settings );
	}

	/**
	 * Test styles
	 */
	public function test_dont_enqueue_styles() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'page';

		$admin = $this->instance();

		$admin->styles();
	}

	/**
	 * Test styles
	 */
	public function test_enqueue_styles() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		WP_Mock::userFunction( 'plugin_dir_url', [ 'times' => 2 ] );
		WP_Mock::userFunction(
			'wp_enqueue_style',
			[
				'args'  => [
					'select2',
					Functions::type( 'string' ),
					[],
					Main::VERSION,
					'all',
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction(
			'wp_enqueue_style',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
					Functions::type( 'string' ),
					[ 'select2' ],
					Main::VERSION,
					'all',
				],
				'times' => 1,
			]
		);

		$admin = $this->instance();

		$admin->styles();
	}

	/**
	 * Test scripts
	 */
	public function test_dont_enqueue_scripts() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'page';

		$admin = $this->instance();

		$admin->scripts();
	}

	/**
	 * Test scripts
	 */
	public function test_enqueue_scripts() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		$admin_url            = '/admin-url/';
		$nonce                = 'nonce123';
		WP_Mock::userFunction( 'plugin_dir_url', [ 'times' => 2 ] );
		WP_Mock::userFunction(
			'admin_url',
			[
				'times'  => 1,
				'return' => $admin_url,
			]
		);
		WP_Mock::userFunction(
			'wp_create_nonce',
			[
				'times'  => 1,
				'return' => $nonce,
			]
		);
		WP_Mock::userFunction(
			'wp_enqueue_script',
			[
				'args'  => [
					'select2',
					Functions::type( 'string' ),
					[ 'jquery' ],
					Main::VERSION,
					true,
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction(
			'wp_enqueue_script',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
					Functions::type( 'string' ),
					[ 'jquery', 'select2' ],
					Main::VERSION,
					true,
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction(
			'wp_localize_script',
			[
				'args' => [
					Main::PLUGIN_SLUG,
					'woo_nova_poshta',
					[
						'url'   => $admin_url,
						'nonce' => $nonce,
					],
				],
			]
		);

		$admin = $this->instance();

		$admin->scripts();
	}

	/**
	 * Test register settings
	 */
	public function test_register_settings() {
		WP_Mock::userFunction(
			'register_setting',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
					Main::PLUGIN_SLUG,
				],
				'times' => 1,
			]
		);

		$admin = $this->instance();
		$admin->register_setting();
	}

	/**
	 * Test adding menu
	 */
	public function test_add_menu() {
		$admin = $this->instance();
		WP_Mock::userFunction( 'plugin_dir_url', [ 'times' => 1 ] );
		WP_Mock::userFunction(
			'add_menu_page',
			[
				'args'  => [
					Main::PLUGIN_NAME,
					Main::PLUGIN_NAME,
					'manage_options',
					Main::PLUGIN_SLUG,
					[
						$admin,
						'page_options',
					],
					Functions::type( 'string' ),
				],
				'times' => 1,
			]
		);

		$admin->add_menu();
	}

	/**
	 * Test page option tab general
	 */
	public function test_page_options_general() {
		WP_Mock::userFunction(
			'wp_verify_nonce',
			[
				'times'  => 1,
				'return' => false,
			]
		);
		WP_Mock::userFunction( 'plugin_dir_path', [ 'times' => 2 ] );
		WP_Mock::userFunction( 'get_admin_url', [ 'times' => 1 ] );
		WP_Mock::userFunction(
			'settings_errors',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction(
			'settings_fields',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
				],
				'times' => 1,
			]
		);
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
		WP_Mock::userFunction( 'selected', [ 'times' => 1 ] );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings->shouldReceive( 'api_key' )->once();
		$settings->shouldReceive( 'phone' )->once();
		$settings
			->shouldReceive( 'city_id' )
			->once()
			->andReturn( $city_id );
		$settings
			->shouldReceive( 'warehouse_id' )
			->once()
			->andReturn( $warehouse_id );
		$admin = new Admin( $api, $settings );

		ob_start();
		$admin->page_options();

		$this->assertTrue( ! empty( ob_get_clean() ) );
	}

	/**
	 * Create invoice request
	 *
	 * @return array
	 */
	public function provider_request() {
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
	 * Test creating invoice controller
	 *
	 * @dataProvider provider_request
	 *
	 * @param array $request Request example.
	 */
	public function test_controller( array $request ) {
		WP_Mock::userFunction(
			'wp_verify_nonce',
			[
				'times'  => 1,
				'return' => true,
			]
		);
		$request_to_api = array_values( $request );
		array_push( $request_to_api, 1 );
		array_push( $request_to_api, 0 );
		FunctionMocker::replace( 'filter_input', $request );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->once()
			->withArgs( $request_to_api );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		WP_Mock::userFunction( 'plugin_dir_path', [ 'times' => 1 ] );
		WP_Mock::userFunction( 'get_admin_url', [ 'times' => 1 ] );
		$admin = new Admin( $api, $settings );
		ob_start();

		$admin->page_options();

		$this->assertTrue( ! empty( ob_get_clean() ) );
	}

	/**
	 * Test page option tab create invoce
	 */
	public function test_page_options_create_invoice() {
		WP_Mock::userFunction(
			'wp_verify_nonce',
			[
				'times'  => 1,
				'return' => false,
			]
		);
		WP_Mock::userFunction( 'plugin_dir_path', [ 'times' => 2 ] );
		WP_Mock::userFunction( 'get_admin_url', [ 'times' => 1 ] );
		WP_Mock::userFunction( 'submit_button', [ 'times' => 1 ] );
		WP_Mock::userFunction(
			'wp_nonce_field',
			[
				'args'  => [
					Main::PLUGIN_SLUG . '-invoice',
					Main::PLUGIN_SLUG . '_nonce',
					false,
				],
				'times' => 1,
			]
		);
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
		$admin    = new Admin( $api, $settings );

		FunctionMocker::replace( 'filter_input', 'internet_document' );
		ob_start();
		$admin->page_options();

		$this->assertTrue( ! empty( ob_get_clean() ) );
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
		WP_Mock::userFunction(
			'add_settings_error',
			[
				'args'  => [
					Main::PLUGIN_SLUG,
					403,
					Functions::type( 'string' ),
				],
				'times' => 1,
			]
		);
		$admin = new Admin( $api, $settings );

		$admin->validate( [ 'api_key' => $key ] );
	}

}
