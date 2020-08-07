<?php
/**
 * Test advertisement
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin\Notice;

use Mockery;
use stdClass;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;

use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Advertisement
 *
 * @package Nova_Poshta\Admin
 */
class Test_Advertisement extends Test_Case {

	/**
	 * Test hooks
	 */
	public function test_hooks() {
		$transient_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$advertisement   = new Advertisement( $transient_cache );
		$advertisement->hooks();

		$this->assertTrue( has_action( 'admin_notices', [ $advertisement, 'notices' ] ) );
		$this->assertTrue(
			has_action(
				'wp_ajax_shipping_nova_poshta_for_woocommerce_notice',
				[
					$advertisement,
					'close',
				]
			)
		);
	}

	/**
	 * Test don't show a notices
	 */
	public function test_do_NOT_show_notices() {
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'some-page';
		$transient_cache      = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'get' )
			->with( 'advertisement' )
			->andReturn( true );
		$advertisement = new Advertisement( $transient_cache );
		$advertisement->notices();
	}

	/**
	 * Show notices
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_show_notices() {
		$message = 'Message';
		global $current_screen;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$current_screen       = new stdClass();
		$current_screen->base = 'some-page';
		$transient_cache      = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'get' )
			->with( 'advertisement' )
			->andReturn( false );
		stubs(
			[
				'__',
				'esc_url',
				'esc_html',
				'esc_attr',
			]
		);
		expect( 'wp_rand' )
			->withNoArgs()
			->once()
			->andReturn( 0 );
		expect( 'plugin_dir_path' )
			->withAnyArgs()
			->once()
			->andReturn( PLUGIN_DIR . '/admin/' );
		expect( 'wp_kses' )
			->with(
				Mockery::type( 'string' ),
				[
					'p'      => [],
					'a'      => [
						'href'   => true,
						'class'  => true,
						'target' => true,
					],
					'strong' => [],
					'span'   => [
						'class' => true,
					],
				]
			)
			->once()
			->andReturn( $message );
		global $current_screen;
		$current_screen->base = 'toplevel_page_' . Main::PLUGIN_SLUG;
		$advertisement        = new Advertisement( $transient_cache );
		ob_start();

		$advertisement->notices();

		$this->assertContains( $message, ob_get_clean() );
	}

	/**
	 * Test close a notice.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_close() {
		expect( 'check_ajax_referer' )
			->with(
				Main::PLUGIN_SLUG,
				'nonce'
			)
			->once();
		$transient_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'set' )
			->with(
				'advertisement',
				1,
				7777
			)
			->once();
		$constant = FunctionMocker::replace( 'constant', 1111 );
		expect( 'wp_send_json' )
			->with( true )
			->once();
		$advertisement = new Advertisement( $transient_cache );

		$advertisement->close();

		$constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
	}

}
