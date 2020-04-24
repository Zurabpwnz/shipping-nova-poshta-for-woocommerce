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
		$shipping = new Shipping();

		WP_Mock::expectActionAdded( 'woocommerce_shipping_init', [ $shipping, 'require_methods' ] );
		WP_Mock::expectFilterAdded( 'woocommerce_shipping_methods', [ $shipping, 'register_methods' ] );

		$shipping->hooks();
	}

	/**
	 * Test register_methods
	 */
	public function test_register_methods() {
		$shipping = new Shipping();

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
	public function test_is_active() {
		global $wpdb;
		$request = 7;
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

		WP_Mock::userFunction( 'wp_cache_get' )->
		withArgs( [ 'shipping_nova_poshta_for_woocommerce_active' ] )->
		once()->
		andReturn( null );
		WP_Mock::userFunction( 'wp_cache_set' )->
		withArgs( [ 'shipping_nova_poshta_for_woocommerce_active', $request ] )->
		once();

		$shipping = new Shipping();

		$this->assertTrue( $shipping->is_active() );
	}

}
