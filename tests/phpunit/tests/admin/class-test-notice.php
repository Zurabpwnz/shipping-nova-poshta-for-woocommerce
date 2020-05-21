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
		$cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$cache
			->shouldReceive( 'get' )
			->with( Notice::NOTICES_KEY )
			->once();
		$notice = new Notice( $cache );

		WP_Mock::expectActionAdded( 'admin_notices', [ $notice, 'notices' ] );
		WP_Mock::expectActionAdded( 'shutdown', [ $notice, 'save' ] );

		$notice->hooks();
	}

	/**
	 * Don't show notices
	 */
	public function test_without_notice() {
		$cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$cache
			->shouldReceive( 'get' )
			->with( Notice::NOTICES_KEY )
			->once();
		$notice = new Notice( $cache );

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

		$cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$cache
			->shouldReceive( 'get' )
			->with( Notice::NOTICES_KEY )
			->once();
		$notice = new Notice( $cache );
		$notice->add( $type, $message );

		ob_start();
		$notice->notices();
		$html = ob_get_clean();

		$this->assertTrue( ! ! strpos( $html, $type ) );
		$this->assertTrue( ! ! strpos( $html, $message ) );
	}

	public function test_save() {
		$cache  = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$notice = 'some-notice';
		$cache
			->shouldReceive( 'get' )
			->with( Notice::NOTICES_KEY )
			->once()
			->andReturn( [ $notice ] );
		$cache
			->shouldReceive( 'delete' )
			->with( Notice::NOTICES_KEY )
			->once();
		$cache
			->shouldReceive( 'set' )
			->with( Notice::NOTICES_KEY, [ $notice ], 60 )
			->once();
		$notice = new Notice( $cache );

		$notice->save();
	}

}
