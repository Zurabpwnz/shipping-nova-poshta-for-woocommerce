<?php
/**
 * Admin notices tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Notice
 *
 * @package Nova_Poshta\Admin
 */
class Test_Notice extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$shipping = Mockery::mock( 'Nova_Poshta\Core\Shipping' );
		$notice   = new Notice( $settings, $shipping );

		WP_Mock::expectActionAdded( 'admin_notices', [ $notice, 'notices' ] );

		$notice->hooks();
	}

	/**
	 * Don't show notices
	 */
	public function test_without_notice() {
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( 'key' );
		$shipping = Mockery::mock( 'Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'is_active' )
			->once()
			->andReturn( true );
		$notice = new Notice( $settings, $shipping );

		$notice->notices();
	}

	/**
	 * Show api key error
	 */
	public function test_show_api_key_error() {
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once();
		$shipping = Mockery::mock( 'Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'is_active' )
			->once()
			->andReturn( true );
		WP_Mock::userFunction( 'wp_kses' )->
		once();
		WP_Mock::userFunction( 'get_admin_url' )->
		with( null, 'admin.php?page=' . Main::PLUGIN_SLUG )->
		once();
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once()->
		andReturn( __DIR__ . '/../../../../admin/' );

		$notice = new Notice( $settings, $shipping );
		ob_start();

		$notice->notices();

		$this->assertTrue( ! ! strpos( ob_get_clean(), 'notice-error' ) );
	}

	/**
	 * Show api key error
	 */
	public function test_shipping_method_enable() {
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( 'api-key' );
		$shipping = Mockery::mock( 'Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'is_active' )
			->once()
			->andReturn( false );
		WP_Mock::userFunction( 'wp_kses' )->
		once();
		WP_Mock::userFunction( 'get_admin_url' )->
		with( null, 'admin.php?page=wc-settings&tab=shipping' )->
		once();
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once()->
		andReturn( __DIR__ . '/../../../../admin/' );

		$notice = new Notice( $settings, $shipping );
		ob_start();

		$notice->notices();

		$this->assertTrue( ! ! strpos( ob_get_clean(), 'notice-error' ) );
	}

}
