<?php
/**
 * Shipping tests
 *
 * @package   Woo-Nova-Poshta
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
	 * Test register_methods
	 */
	public function test_register_methods() {
		$shipping = new Shipping();

		$this->assertSame(
			[
				'woo_nova_poshta' => 'Nova_Poshta_Shipping_Method',
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
			WHERE `method_id` = "woo_nova_poshta" AND `is_enabled` = 1 LIMIT 1';
		$wpdb
			->shouldReceive( 'prepare' )
			->withArgs(
				[
					'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = %s AND `is_enabled` = 1 LIMIT 1',
					'woo_nova_poshta',
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
		withArgs( [ 'woo_nova_poshta_active' ] )->
		once()->
		andReturn( null );
		WP_Mock::userFunction( 'wp_cache_set' )->
		withArgs( [ 'woo_nova_poshta_active', $request ] )->
		once();

		$shipping = new Shipping();

		$this->assertTrue( $shipping->is_active() );
	}

}
