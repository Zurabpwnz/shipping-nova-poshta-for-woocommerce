<?php
/**
 * Main tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;

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
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_init() {
		expect( 'is_plugin_active' )
			->with( 'woocommerce/woocommerce.php' )
			->once()
			->andReturn( false );
		when( '__' )->returnArg();
		$notice = Mockery::mock( 'overload:Nova_Poshta\Admin\Notice\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', '<strong>' . Main::PLUGIN_NAME . '</strong> extends WooCommerce functionality and does not work without it.' )
			->once();
		$notice
			->shouldReceive( 'hooks' )
			->once();
		Mockery::mock( 'overload:Nova_Poshta\Core\Cache\Factory_Cache' );
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
		$settings
			->shouldReceive( 'is_shipping_cost_enable' )
			->once()
			->andReturn( true );
		$shipping = Mockery::mock( 'overload:Nova_Poshta\Core\Shipping' );
		$shipping
			->shouldReceive( 'hooks' )
			->once();
		$payment = Mockery::mock( 'overload:Nova_Poshta\Core\Payment' );
		$payment
			->shouldReceive( 'hooks' )
			->once();
		$language = Mockery::mock( 'overload:Nova_Poshta\Core\Language' );
		$language
			->shouldReceive( 'hooks' )
			->once();
		$db = Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		$db
			->shouldReceive( 'hooks' )
			->once();
		$api = Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'hooks' )
			->once();
		$admin = Mockery::mock( 'overload:Nova_Poshta\Admin\Admin' );
		$admin
			->shouldReceive( 'hooks' )
			->once();

		Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Shipping_Cost' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = Mockery::mock( 'overload:Nova_Poshta\Admin\Admin_Manage_Orders' );
		$admin_manage_orders
			->shouldReceive( 'hooks' )
			->once();
		$advertisement = Mockery::mock( 'overload:Nova_Poshta\Admin\Notice\Advertisement' );
		$advertisement
			->shouldReceive( 'hooks' )
			->once();
		$ajax = Mockery::mock( 'overload:Nova_Poshta\Core\AJAX' );
		$ajax
			->shouldReceive( 'hooks' )
			->once();
		$checkout = Mockery::mock( 'overload:Nova_Poshta\Core\Checkout' );
		$checkout
			->shouldReceive( 'hooks' )
			->once();
		$front = Mockery::mock( 'overload:Nova_Poshta\Front\Front' );
		$front
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
		$user = Mockery::mock( 'overload:Nova_Poshta\Admin\User' );
		$user
			->shouldReceive( 'hooks' )
			->once();
		$product_cat_metabox = Mockery::mock( 'overload:Nova_Poshta\Admin\Product_Category_Metabox' );
		$product_cat_metabox
			->shouldReceive( 'hooks' )
			->once();
		$product_metabox = Mockery::mock( 'overload:Nova_Poshta\Admin\Product_Metabox' );
		$product_metabox
			->shouldReceive( 'hooks' )
			->once();

		$main = new Main();

		$main->init();
	}

}
