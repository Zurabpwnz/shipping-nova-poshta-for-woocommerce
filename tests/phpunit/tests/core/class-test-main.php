<?php
/**
 * Main tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;

/**
 * Class Test_Main
 *
 * @package Nova_Poshta\Core
 */
class Test_Main extends Test_Case {

	/**
	 * Test init all hooks
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init() {
		\WP_Mock::userFunction( 'is_plugin_active' )->
		with( 'woocommerce/woocommerce.php' )->
		once()->
		andReturn( false );
		$notice = Mockery::mock( 'overload:Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', '<strong>' . Main::PLUGIN_NAME . '</strong> extends WooCommerce functionality and does not work without it.' )
			->once();
		$notice
			->shouldReceive( 'hooks' )
			->once();
		$object_cache = Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Object_Cache' );
		$object_cache
			->shouldReceive( 'hooks' )
			->once();
		$transient_cache = Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Transient_Cache' );
		$transient_cache
			->shouldReceive( 'hooks' )
			->once();
		$settings = Mockery::mock( 'overload:Nova_Poshta\Core\Settings' );
		$settings
			->shouldReceive( 'api_key' )
			->once()
			->andReturn( 'api-key' );
		$db = Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'hooks' )
			->once();
		$api = Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'hooks' )
			->once();
		$ajax = Mockery::mock( 'overload:Nova_Poshta\Core\AJAX' );
		$ajax
			->shouldReceive( 'hooks' )
			->once();
		$admin = Mockery::mock( 'overload:Nova_Poshta\Admin\Admin' );
		$admin
			->shouldReceive( 'hooks' )
			->once();
		$user = Mockery::mock( 'overload:Nova_Poshta\Admin\User' );
		$user
			->shouldReceive( 'hooks' )
			->once();
		$front = Mockery::mock( 'overload:Nova_Poshta\Front\Front' );
		$front
			->shouldReceive( 'hooks' )
			->once();
		$shipping = Mockery::mock( 'overload:Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'hooks' )
			->once();
		$checkout = Mockery::mock( 'overload:Nova_Poshta\Core\Checkout' );
		$checkout
			->shouldReceive( 'hooks' )
			->once();
		$order = Mockery::mock( 'overload:Nova_Poshta\Core\Order' );
		$order
			->shouldReceive( 'hooks' )
			->once();
		$thank_you = Mockery::mock( 'overload:Nova_Poshta\Core\Thank_You' );
		$thank_you
			->shouldReceive( 'hooks' )
			->once();
		$language = Mockery::mock( 'overload:Nova_Poshta\Core\Language' );

		$main = new Main();

		$main->init();
	}

}
