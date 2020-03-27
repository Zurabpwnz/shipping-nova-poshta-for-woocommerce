<?php
/**
 * Admin user tests
 *
 * @package   Woo-Nova-Poshta
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
class Test_User extends Test_Case {

	public function test_fields() {
		$city_id    = 'city-id';
		$city       = 'City';
		$warehouses = [
			'warehouse-id-1' => 'Warehouse',
			'warehouse-id-2' => 'Warehouse 2',
		];
		$api        = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->once()
			->andReturn( [ $city_id => $city ] );
		$api
			->shouldReceive( 'warehouses' )
			->once()
			->withArgs( [ $city_id ] )
			->andReturn( $warehouses );
		WP_Mock::userFunction(
			'wp_nonce_field',
			[
				'args'  => [
					Main::PLUGIN_SLUG . '-shipping',
					'woo_nova_poshta_nonce',
					false,
				],
				'times' => 1,
			]
		);
		WP_Mock::userFunction( 'woocommerce_form_field', [ 'times' => 2 ] );
		$user = new User( $api );

		$user->fields();
	}

	public function test_dont_save_on_checkout() {
		WP_Mock::userFunction( 'get_current_user_id' )
		       ->once();
		$api  = Mockery::mock( 'Nova_Poshta\Core\API' );
		$user = new User( $api );

		$user->checkout();
	}

	public function test_not_valid_nonce_on_checkout() {
//		WP_Mock::userFunction(
//			'get_current_user_id',
//			[
//				'times'  => 1,
//				'return' => 777,
//			]
//		);
//		WP_Mock::userFunction(
//			'',
//			'',
//			''
//		);
		$api  = Mockery::mock( 'Nova_Poshta\Core\API' );
		$user = new User( $api );

		$user->checkout();
	}

	/**
	 * $user_id = get_current_user_id();
	 * if ( ! $user_id ) {
	 * return;
	 * }
	 * $nonce = filter_input( INPUT_POST, 'woo_nova_poshta_nonce', FILTER_SANITIZE_STRING );
	 * if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
	 * return;
	 * }
	 *
	 * $city_id      = filter_input( INPUT_POST, 'woo_nova_poshta_city', FILTER_SANITIZE_STRING );
	 * $warehouse_id = filter_input( INPUT_POST, 'woo_nova_poshta_warehouse', FILTER_SANITIZE_STRING );
	 * if ( ! $city_id || ! $warehouse_id ) {
	 * return;
	 * }
	 * update_user_meta( $user_id, 'woo_nova_poshta_city', $city_id );
	 * update_user_meta( $user_id, 'woo_nova_poshta_warehouse', $warehouse_id );
	 */

}
