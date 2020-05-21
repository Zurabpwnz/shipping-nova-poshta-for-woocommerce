<?php
/**
 * Order tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Exception;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Order
 *
 * @package Nova_Poshta\Core
 */
class Test_Order extends Test_Case {

	/**
	 * End test
	 */
	public function tearDown() {
		parent::tearDown();
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST );
	}

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$order         = new Order( $api, $shipping_cost, $notice );

		WP_Mock::expectActionAdded( 'woocommerce_checkout_create_order_shipping_item', [ $order, 'create' ], 10, 4 );
		WP_Mock::expectActionAdded( 'woocommerce_before_order_item_object_save', [ $order, 'save' ] );
		WP_Mock::expectActionAdded( 'woocommerce_checkout_update_customer', [ $order, 'update_nonce_for_new_users' ] );
		WP_Mock::expectActionAdded( 'woocommerce_order_actions', [ $order, 'register_order_actions' ] );
		WP_Mock::expectActionAdded(
			'woocommerce_order_action_nova_poshta_create_internet_document',
			[
				$order,
				'create_internet_document',
			]
		);
		WP_Mock::expectActionAdded( 'woocommerce_order_status_processing', [ $order, 'processing_status' ], 10, 2 );
		WP_Mock::expectActionAdded(
			'woocommerce_before_order_itemmeta',
			[
				$order,
				'default_fields_for_shipping_item',
			],
			10,
			2
		);
		WP_Mock::expectFilterAdded( 'woocommerce_order_item_display_meta_key', [ $order, 'labels' ], 10, 2 );
		WP_Mock::expectFilterAdded( 'woocommerce_order_item_display_meta_value', [ $order, 'values' ], 10, 2 );

		$order->hooks();
	}

	/**
	 * Test updating nonce after registration new user
	 */
	public function test_update_nonce_for_new_users() {
		$nonce     = 'some-nonce';
		$new_nonce = 'some-new-nonce';
		FunctionMocker::replace( 'filter_input', $nonce );
		WP_Mock::userFunction( 'wp_create_nonce' )->
		withArgs( [ Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( $new_nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->update_nonce_for_new_users();
		//phpcs:disable WordPress.Security.NonceVerification.Missing
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		//phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$this->assertSame( $new_nonce, $_POST['shipping_nova_poshta_for_woocommerce_nonce'] );
		//phpcs:enable WordPress.Security.NonceVerification.Missing
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		//phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	/**
	 * Test don't save with empty nonce
	 */
	public function test_do_NOT_save_with_empty_nonce() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$order         = new Order( $api, $shipping_cost, $notice );

		$order->create( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save with bad nonce
	 */
	public function test_do_NOT_save_with_bad_nonce() {
		$nonce = 'nonce';

		$_POST['shipping_nova_poshta_for_woocommerce_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( false );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save for other shipping method
	 */
	public function test_do_NOT_save_for_other_shipping_method() {
		$nonce = 'nonce';

		$_POST['shipping_nova_poshta_for_woocommerce_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'no_shipping_nova_poshta_for_woocommerce' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test don't save with not enough dating
	 */
	public function test_do_NOT_save_with_empty_city_or_warehouse() {
		$nonce = 'nonce';

		$_POST['shipping_nova_poshta_for_woocommerce_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Test save with fail nonce
	 */
	public function test_create() {
		global $city_id, $warehouse_id;
		$nonce        = 'nonce';
		$city_id      = 'city-id';
		$warehouse_id = 'warehouse-id';

		$_POST['shipping_nova_poshta_for_woocommerce_nonce'] = $nonce;
		WP_Mock::userFunction( 'wp_unslash' )->
		withArgs( [ $nonce ] )->
		once()->
		andReturn( $nonce );
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		withArgs( [ $nonce, Main::PLUGIN_SLUG . '-shipping' ] )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'filter_var', $nonce );
		$answers = [ $city_id, $warehouse_id ];
		$answers = array_values( $answers );

		FunctionMocker::replace(
			'filter_input',
			function () use ( $answers ) {
				static $i = 0;

				return $answers[ $i ++ ];
			}
		);
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'city_id', $city_id, true ] )
			->once();
		$item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'warehouse_id', $warehouse_id, true ] )
			->once();
		$package_key   = 10;
		$package       = [];
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create( $item_shipping, $package_key, $package, $wc_order );
	}

	/**
	 * Don't save in not admin area
	 */
	public function test_save_in_NOT_admin_area() {
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( false );
		$order_item    = Mockery::mock( 'WC_Order_Item' );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 * Don't save on not shipping method WC_Order_Item_Shipping
	 */
	public function test_save_NOT_shipping_method() {
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( true );
		$order_item    = Mockery::mock( 'WC_Order_Item' );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 *  Don't save with othe shipping method
	 */
	public function test_save_NOT_poshta_shipping_method() {
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'is_a', true );
		$order_item = Mockery::mock( 'WC_Order_Item' );
		$order_item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'other-shipping-method' );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 * Don't save without order shipping city
	 */
	public function test_save_without_city() {
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'is_a', true );
		$order_item = Mockery::mock( 'WC_Order_Item' );
		$order_item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$order_item
			->shouldReceive( 'get_meta' )
			->with( 'city_id', true )
			->once()
			->andReturn( false );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 * Don't save without global $woocommerce->cart
	 */
	public function test_save_without_cart_items() {
		$city_id = 'city-id';
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'is_a', true );
		$order_item = Mockery::mock( 'WC_Order_Item' );
		$order      = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( false );
		$order_item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$order_item
			->shouldReceive( 'get_meta' )
			->with( 'city_id', true )
			->once()
			->andReturn( $city_id );
		$order_item
			->shouldReceive( 'get_order' )
			->once()
			->andReturn( $order );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 * Test calculate shipping on save order
	 */
	public function test_save() {
		$city_id   = 'city-id';
		$cost      = 48;
		$quantity1 = 10;
		$quantity2 = 12;
		$product1  = Mockery::mock( 'WC_Product' );
		$product2  = Mockery::mock( 'WC_Product' );
		WP_Mock::userFunction( 'is_admin' )->
		once()->
		andReturn( true );
		FunctionMocker::replace( 'is_a', true );
		$order = Mockery::mock( 'WC_Order' );
		$item1 = Mockery::mock( 'WC_Order_Product_Item' );
		$item1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $quantity1 );
		$item1
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product1 );
		$item2 = Mockery::mock( 'WC_Order_Product_Item' );
		$item2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $quantity2 );
		$item2
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product2 );
		$order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $item1, $item2 ] );
		$order_item = Mockery::mock( 'WC_Order_Item' );
		$order_item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$order_item
			->shouldReceive( 'get_meta' )
			->with( 'city_id', true )
			->once()
			->andReturn( $city_id );
		$order_item
			->shouldReceive( 'set_total' )
			->with( $cost );
		$order_item
			->shouldReceive( 'get_order' )
			->once()
			->andReturn( $order );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'calculate' )
			->with(
				$city_id,
				[
					[
						'quantity' => $quantity1,
						'data'     => $product1,
					],
					[
						'quantity' => $quantity2,
						'data'     => $product2,
					],
				]
			)
			->once()
			->andReturn( $cost );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->save( $order_item );
	}

	/**
	 * Label cases
	 *
	 * @return array
	 */
	public function dp_labels() {
		return [
			[
				'key',
				'key',
			],
			[
				'city_id',
				'City',
			],
			[
				'warehouse_id',
				'Warehouse',
			],
			[
				'internet_document',
				'Invoice',
			],
		];
	}

	/**
	 * Test correct names labels
	 *
	 * @dataProvider dp_labels
	 *
	 * @param string $key    Key name.
	 * @param string $result Key result.
	 */
	public function test_labels( string $key, string $result ) {
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->between( 1, 3 )
			->andReturn( $key );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$this->assertSame( $result, $order->labels( $key, $wc_meta_data ) );
	}

	/**
	 * Test correct city value
	 */
	public function test_city_value() {
		$key       = 'city_id';
		$value     = 'city-id';
		$city_name = 'City Name';
		$api       = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->withArgs( [ $value ] )
			->once()
			->andReturn( $city_name );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->once()
			->andReturn( $key );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'value' ] )
			->twice()
			->andReturn( $value );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$this->assertSame( $city_name, $order->values( $value, $wc_meta_data ) );
	}

	/**
	 * Test correct warehouse value
	 */
	public function test_warehouse_value() {
		$key            = 'warehouse_id';
		$value          = 'warehouse-id';
		$warehouse_name = 'Warehouse Name';
		$api            = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'warehouse' )
			->withArgs( [ $value ] )
			->once()
			->andReturn( $warehouse_name );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->twice()
			->andReturn( $key );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'value' ] )
			->twice()
			->andReturn( $value );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$this->assertSame( $warehouse_name, $order->values( $value, $wc_meta_data ) );
	}

	/**
	 * Test other values
	 */
	public function test_values() {
		$key          = 'other_key';
		$value        = 'other_value';
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_meta_data = Mockery::mock( 'WC_Meta_Data' );
		$wc_meta_data
			->shouldReceive( '__get' )
			->withArgs( [ 'key' ] )
			->twice()
			->andReturn( $key );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$this->assertSame( $value, $order->values( $value, $wc_meta_data ) );
	}

	/**
	 * Test default fileds for not shipping order item
	 */
	public function test_default_fields_for_order_item() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item          = Mockery::mock( '\WC_Order_Item' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->default_fields_for_shipping_item( 10, $item );
	}

	/**
	 * Test default fileds for other shipping order item
	 */
	public function test_default_fields_for_other_shipping_item() {
		$api  = Mockery::mock( 'Nova_Poshta\Core\API' );
		$item = Mockery::mock( '\WC_Order_Item' );
		$item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'other_shipping_nova_poshta_for_woocommerce' );
		FunctionMocker::replace( 'is_a', true );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->default_fields_for_shipping_item( 10, $item );
	}

	/**
	 * Test default fileds for other shipping order item
	 */
	public function test_default_fields_for_shipping_item() {
		$city_id      = 'city-id';
		$warehouse_id = 'warehouse-id';
		$api          = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'cities' )
			->withArgs( [ '', 1 ] )
			->once()
			->andReturn( [ $city_id => 'City Name' ] );
		$api
			->shouldReceive( 'warehouses' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( [ $warehouse_id => 'Warehouse Name' ] );
		$item = Mockery::mock( '\WC_Order_Item' );
		$item
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$item
			->shouldReceive( 'get_meta' )
			->once()
			->withArgs( [ 'city_id' ] )
			->andReturn( false );
		$item
			->shouldReceive( 'get_meta' )
			->once()
			->withArgs( [ 'city_id' ] )
			->andReturn( $city_id );
		$item
			->shouldReceive( 'update_meta_data' )
			->once()
			->withArgs( [ 'city_id', $city_id ] );
		$item
			->shouldReceive( 'get_meta' )
			->once()
			->withArgs( [ 'warehouse_id' ] );
		$item
			->shouldReceive( 'update_meta_data' )
			->once()
			->withArgs( [ 'warehouse_id', $warehouse_id ] );
		$item
			->shouldReceive( 'save_meta_data' )
			->once();
		FunctionMocker::replace( 'is_a', true );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->default_fields_for_shipping_item( 10, $item );
	}

	/**
	 * Test adding new order actions
	 */
	public function test_register_order_actions() {
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$this->assertSame(
			[ 'nova_poshta_create_internet_document' => 'Create Nova Poshta Internet Document' ],
			$order->register_order_actions( [] )
		);
	}

	/**
	 * Test creating internet document with not enough permissions
	 */
	public function test_processing_status_for_not_enough_permissions() {
		WP_Mock::userFunction( 'current_user_can' )->
		once()->
		andReturn( false );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$order = new Order( $api, $shipping_cost, $notice );

		$order->processing_status( 10, $wc_order );
	}

	/**
	 * Test creating internet document with not enough permissions
	 */
	public function test_processing_status() {
		WP_Mock::userFunction( 'current_user_can' )->
		once()->
		andReturn( true );
		$api           = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_order      = Mockery::mock( 'WC_Order' );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$stub          = Mockery::mock(
			'Nova_Poshta\Core\Order[create_internet_document]',
			[
				$api,
				$shipping_cost,
				$notice,
			]
		);
		$stub
			->shouldReceive( 'create_internet_document' )
			->once();

		$stub->processing_status( 10, $wc_order );
	}

	/**
	 * Dont create internet document without shipping method
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_do_NOT_create_invoice_without_shipping_method() {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$order         = new Order( $api, $shipping_cost, $notice );

		$order->create_internet_document( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_NOT_create_invoice_repeatedly() {
		$internet_document      = '1234 5678 9012 3456';
		$api                    = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_order_item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'internet_document' )
			->once()
			->andReturn( $internet_document );
		$wc_order_item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $wc_order_item_shipping ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'The invoice was created before' )
			->once();

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create_internet_document( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_create_invoice() {
		$first_name        = 'First name';
		$last_name         = 'Last name';
		$phone             = '+380123456789';
		$total             = 10;
		$city_id           = 'city-id';
		$warehouse_id      = 'warehouse-id';
		$internet_document = '1234 5678 9012 3456';
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->withArgs(
				[
					$first_name,
					$last_name,
					$phone,
					$city_id,
					$warehouse_id,
					$total,
					15,
				]
			)
			->once()
			->andReturn( $internet_document );
		$wc_order_item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'internet_document' )
			->once()
			->andReturn( false );
		$wc_order_item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once()
			->andReturn( $city_id );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once()
			->andReturn( $warehouse_id );
		$wc_order_item_shipping
			->shouldReceive( 'add_meta_data' )
			->withArgs( [ 'internet_document', $internet_document, true ] )
			->once();
		$wc_order_item_shipping
			->shouldReceive( 'save_meta_data' )
			->once();
		$wc_order_item_1 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 5 );
		$wc_order_item_2 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 10 );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $wc_order_item_shipping ] );
		$wc_order
			->shouldReceive( 'get_billing_first_name' )
			->once()
			->andReturn( $first_name );
		$wc_order
			->shouldReceive( 'get_billing_last_name' )
			->once()
			->andReturn( $last_name );
		$wc_order
			->shouldReceive( 'get_billing_phone' )
			->once()
			->andReturn( $phone );
		$wc_order
			->shouldReceive( 'get_total' )
			->once()
			->andReturn( $total );
		$wc_order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $wc_order_item_1, $wc_order_item_2 ] );
		$wc_order
			->shouldReceive( 'add_order_note' )
			->with( 'Created Internet document for Nova Poshta' )
			->once();
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'success', 'The invoice will successfully create' )
			->once();

		$order = new Order( $api, $shipping_cost, $notice );

		$order->create_internet_document( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_create_invoice_with_api_error() {
		$first_name        = 'First name';
		$last_name         = 'Last name';
		$phone             = '+380123456789';
		$total             = 10;
		$city_id           = 'city-id';
		$warehouse_id      = 'warehouse-id';
		$internet_document = '1234 5678 9012 3456';
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->withArgs(
				[
					$first_name,
					$last_name,
					$phone,
					$city_id,
					$warehouse_id,
					$total,
					15,
				]
			)
			->once()
			->andReturn( false );
		$api
			->shouldReceive( 'errors' )
			->with()
			->andReturn( [ 'Error message 1', 'Error message 2' ] );
		$wc_order_item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'internet_document' )
			->once()
			->andReturn( false );
		$wc_order_item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once()
			->andReturn( $city_id );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once()
			->andReturn( $warehouse_id );
		$wc_order_item_1 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 5 );
		$wc_order_item_2 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( 10 );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $wc_order_item_shipping ] );
		$wc_order
			->shouldReceive( 'get_billing_first_name' )
			->once()
			->andReturn( $first_name );
		$wc_order
			->shouldReceive( 'get_billing_last_name' )
			->once()
			->andReturn( $last_name );
		$wc_order
			->shouldReceive( 'get_billing_phone' )
			->once()
			->andReturn( $phone );
		$wc_order
			->shouldReceive( 'get_total' )
			->once()
			->andReturn( $total );
		$wc_order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $wc_order_item_1, $wc_order_item_2 ] );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'The invoice wasn\'t created because:' )
			->once();
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'Error message 1' )
			->once();
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'Error message 2' )
			->once();


		$order = new Order( $api, $shipping_cost, $notice );

		$order->create_internet_document( $wc_order );
	}

}
