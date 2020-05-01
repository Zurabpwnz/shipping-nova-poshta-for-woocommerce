<?php
/**
 * Shipping tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

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
		$notice       = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( Shipping::METHOD_NAME . '_active' )
			->once()
			->andReturn( true );

		$shipping = new Shipping( $notice, $object_cache );

		WP_Mock::expectActionAdded( 'woocommerce_shipping_init', [ $shipping, 'require_methods' ] );
		WP_Mock::expectFilterAdded( 'woocommerce_shipping_methods', [ $shipping, 'register_methods' ] );

		$shipping->hooks();
	}

	/**
	 * Test register_methods
	 */
	public function test_register_methods() {
		$notice       = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$object_cache = Mockery::mock( 'Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'get' )
			->with( Shipping::METHOD_NAME . '_active' )
			->once()
			->andReturn( true );

		$shipping = new Shipping( $notice, $object_cache );

		$this->assertSame(
			[
				'shipping_nova_poshta_for_woocommerce' => 'Nova_Poshta_Shipping_Method',
			],
			$shipping->register_methods( [] )
		);
	}

	/**
	 * Check active nova poshta shipping method
	 */
	public function test_notices() {
		global $wpdb;
		$request = false;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wpdb         = Mockery::mock( 'wpdb' );
		$wpdb->prefix = 'prefix_';
		$sql          = 'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = "shipping_nova_poshta_for_woocommerce" AND `is_enabled` = 1 LIMIT 1';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = %s AND `is_enabled` = 1 LIMIT 1',
					'shipping_nova_poshta_for_woocommerce',
				]
			)
			->once()
			->andReturn( $sql );
		$wpdb
			->shouldReceive( 'get_var' )
			->withArgs( [ $sql ] )
			->once()
			->andReturn( $request );
		WP_Mock::userFunction( 'get_admin_url' )->
		with( null, 'admin.php?page=wc-settings&tab=shipping' )->
		once()->
		andReturn( 'url' );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with(
				'error',
				'You must add the "New Delivery Method" delivery method <a href="url">in the WooCommerce settings</a>'
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
			->with( Shipping::METHOD_NAME . '_active', $request )
			->once();

		new Shipping( $notice, $object_cache );
	}

}
