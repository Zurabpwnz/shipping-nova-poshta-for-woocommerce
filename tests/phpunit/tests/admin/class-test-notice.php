<?php
/**
 * Admin notices tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Admin;

use Mockery;
use Nova_Poshta\Tests\Test_Case;

/**
 * Class Test_Notice
 *
 * @package Nova_Poshta\Admin
 */
class Test_Notice extends Test_Case {

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
	 * Show all notices
	 */
	public function test_show_all_notice() {
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once();
		$shipping = Mockery::mock( 'Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'is_active' )
			->once()
			->andReturn( false );
		\WP_Mock::userFunction( 'wp_kses', [ 'times' => 2 ] );
		$notice = new Notice( $settings, $shipping );
		ob_start();

		$notice->notices();

		$this->assertTrue( ! empty( ob_get_clean() ) );
	}

}