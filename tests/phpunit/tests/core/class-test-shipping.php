<?php
/**
 * Shipping tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_Shipping
 *
 * @package Nova_Poshta\Core
 */
class Test_Shipping extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$notice       = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( Shipping::METHOD_NAME . '_active' )
			->once()
			->andReturn( true );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'object' )
			->once()
			->andReturn( $object_cache );

		$shipping = new Shipping( $notice, $factory_cache );

		$shipping->hooks();

		$this->assertTrue(
			has_action( 'woocommerce_shipping_init', [ $shipping, 'require_methods' ] )
		);
		$this->assertTrue(
			has_filter( 'woocommerce_shipping_methods', [ $shipping, 'register_methods' ] )
		);
	}

	/**
	 * Test register_methods
	 */
	public function test_register_methods() {
		$notice       = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( Shipping::METHOD_NAME . '_active' )
			->once()
			->andReturn( true );
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'object' )
			->once()
			->andReturn( $object_cache );

		$shipping = new Shipping( $notice, $factory_cache );

		$this->assertSame(
			[
				'shipping_nova_poshta_for_woocommerce' => 'Nova_Poshta_Shipping_Method',
			],
			$shipping->register_methods( [] )
		);
	}

	/**
	 * Check active nova poshta shipping method
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_notices() {
		when( '__' )->returnArg();
		expect( 'get_admin_url' )
			->with( null, 'admin.php?page=wc-settings&tab=shipping' )
			->once()
			->andReturn( 'url' );
		global $wpdb;
		$request = false;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb           = Mockery::mock( 'wpdb' );
		$wpdb->prefix   = 'prefix_';
		$day_in_seconds = 1234;
		$constant       = FunctionMocker::replace( 'constant', $day_in_seconds );
		$sql            = 'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = "shipping_nova_poshta_for_woocommerce" AND `is_enabled` = 1 LIMIT 1';
		$wpdb
			->shouldReceive( 'prepare' )
			->with(
				'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = %s AND `is_enabled` = 1 LIMIT 1',
				'shipping_nova_poshta_for_woocommerce'
			)
			->once()
			->andReturn( $sql );
		$wpdb
			->shouldReceive( 'get_var' )
			->with( $sql )
			->once()
			->andReturn( $request );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with(
				'error',
				'You must add the "Nova Poshta" shipping method <a href="url">in the WooCommerce settings</a>'
			)
			->once();
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( Shipping::METHOD_NAME . '_active' )
			->once()
			->andReturn( false );
		$object_cache
			->shouldReceive( 'set' )
			->with( Shipping::METHOD_NAME . '_active', $request, $day_in_seconds )
			->once();
		$factory_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Factory_Cache' );
		$factory_cache
			->shouldReceive( 'object' )
			->once()
			->andReturn( $object_cache );

		new Shipping( $notice, $factory_cache );

		$constant->wasCalledWithOnce( [ 'DAY_IN_SECONDS' ] );
	}

}
