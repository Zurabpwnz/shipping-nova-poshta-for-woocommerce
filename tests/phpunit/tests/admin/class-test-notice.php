<?php
/**
 * Admin notices tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
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
		$notice = new Notice();

		ob_start();
		$notice->notices();

		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Show notices
	 */
	public function test_show_notice() {

		$type    = 'type';
		$message = 'message';
		WP_Mock::userFunction( 'wp_kses' )->
		with(
			$message,
			[
				'a'      => [ 'href' => true ],
				'strong' => [],
			]
		)->
		once()->
		andReturn( $message );

		$notice = new Notice();
		$notice->add( $type, $message );

		ob_start();
		$notice->notices();
		$html = ob_get_clean();

		$this->assertTrue( ! ! strpos( $html, $type ) );
		$this->assertTrue( ! ! strpos( $html, $message ) );
	}

}
