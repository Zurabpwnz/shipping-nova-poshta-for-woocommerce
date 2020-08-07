<?php
/**
 * Internet Document tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta_Gateway_COD;
use Nova_Poshta\Tests\Test_Case;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Internet_Document
 *
 * @package Nova_Poshta\Core
 */
class Test_Internet_Document extends Test_Case {

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
		$shipping_cost     = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice            = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$internet_document = new Internet_Document( $api, $shipping_cost, $notice );

		$internet_document->create( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_NOT_create_invoice_repeatedly() {
		$internet_document = '1234 5678 9012 3456';
		when( '__' )->returnArg();
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
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'The invoice was created before' )
			->once();
		$internet_document = new Internet_Document( $api, $shipping_cost, $notice );

		$internet_document->create( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_NOT_create_invoice_WITHOUT_items() {
		$internet_document = '1234 5678 9012 3456';
		when( '__' )->returnArg();
		$api                    = Mockery::mock( 'Nova_Poshta\Core\API' );
		$wc_order_item_shipping = Mockery::mock( 'WC_Order_Item_Shipping' );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'internet_document' )
			->once();
		$wc_order_item_shipping
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $wc_order_item_shipping ] );
		$wc_order
			->shouldReceive( 'get_items' )
			->once();
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$notice        = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'error', 'The order doesn\'t have a products' )
			->once();
		$internet_document = new Internet_Document( $api, $shipping_cost, $notice );

		$internet_document->create( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_create_invoice() {
		when( '__' )->returnArg();
		$first_name         = 'First name';
		$last_name          = 'Last name';
		$phone              = '+380123456789';
		$city_id            = 'city-id';
		$warehouse_id       = 'warehouse-id';
		$internet_document  = '1234 5678 9012 3456';
		$product_1          = 'product 1';
		$product_1_quantity = 5;
		$product_2          = 'product 2';
		$product_2_quantity = 10;
		$weight             = 11;
		$volume             = 22;
		$total              = 777;
		$prepayment         = 111;
		$shipping_total     = 31;
		expect( 'get_option' )
			->with( Nova_Poshta_Gateway_COD::ID . '_settings' )
			->once()
			->andReturn( [ 'prepayment' => $prepayment ] );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->with(
				$first_name,
				$last_name,
				$phone,
				$city_id,
				$warehouse_id,
				$total - $shipping_total,
				$weight,
				$volume,
				$total - $shipping_total - $prepayment
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
			->with( 'city_id' )
			->once()
			->andReturn( $city_id );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'warehouse_id' )
			->once()
			->andReturn( $warehouse_id );
		$wc_order_item_shipping
			->shouldReceive( 'add_meta_data' )
			->with( 'internet_document', $internet_document, true )
			->once();
		$wc_order_item_shipping
			->shouldReceive( 'save_meta_data' )
			->once();
		$wc_order_item_1 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $product_1_quantity );
		$wc_order_item_1
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product_1 );
		$wc_order_item_2 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $product_2_quantity );
		$wc_order_item_2
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product_2 );
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
			->shouldReceive( 'get_shipping_total' )
			->once()
			->andReturn( $shipping_total );
		$wc_order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $wc_order_item_1, $wc_order_item_2 ] );
		$wc_order
			->shouldReceive( 'get_payment_method' )
			->withNoArgs()
			->once()
			->andReturn( Nova_Poshta_Gateway_COD::ID );
		$wc_order
			->shouldReceive( 'add_order_note' )
			->with( 'Created Internet document for Nova Poshta' )
			->once();
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'get_products_weight' )
			->with(
				[
					[
						'quantity' => $product_1_quantity,
						'data'     => $product_1,
					],
					[
						'quantity' => $product_2_quantity,
						'data'     => $product_2,
					],
				]
			)
			->once()
			->andReturn( $weight );
		$shipping_cost
			->shouldReceive( 'get_products_volume' )
			->with(
				[
					[
						'quantity' => $product_1_quantity,
						'data'     => $product_1,
					],
					[
						'quantity' => $product_2_quantity,
						'data'     => $product_2,
					],
				]
			)
			->once()
			->andReturn( $volume );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with( 'success', 'The invoice will successfully create' )
			->once();
		$internet_document = new Internet_Document( $api, $shipping_cost, $notice );

		$internet_document->create( $wc_order );
	}

	/**
	 * Create internet document
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function test_create_invoice_with_api_error() {
		when( '__' )->returnArg();
		$first_name         = 'First name';
		$last_name          = 'Last name';
		$phone              = '+380123456789';
		$city_id            = 'city-id';
		$warehouse_id       = 'warehouse-id';
		$internet_document  = '1234 5678 9012 3456';
		$product_1          = 'product 1';
		$product_1_quantity = 5;
		$product_2          = 'product 2';
		$product_2_quantity = 10;
		$weight             = 11;
		$volume             = 22;
		$total              = 777;
		$prepayment         = 111;
		$shipping_total     = 31;
		expect( 'get_option' )
			->with( Nova_Poshta_Gateway_COD::ID . '_settings' )
			->once()
			->andReturn( [ 'prepayment' => $prepayment ] );
		$api = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'internet_document' )
			->with(
				$first_name,
				$last_name,
				$phone,
				$city_id,
				$warehouse_id,
				$total - $shipping_total,
				$weight,
				$volume,
				$total - $shipping_total - $prepayment
			)
			->once()
			->andReturn( false );
		$api
			->shouldReceive( 'errors' )
			->withNoArgs()
			->once()
			->andReturn(
				[
					'Error message 1',
					'Error message 2',
				]
			);
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
			->with( 'city_id' )
			->once()
			->andReturn( $city_id );
		$wc_order_item_shipping
			->shouldReceive( 'get_meta' )
			->with( 'warehouse_id' )
			->once()
			->andReturn( $warehouse_id );
		$wc_order_item_1 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_1
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $product_1_quantity );
		$wc_order_item_1
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product_1 );
		$wc_order_item_2 = Mockery::mock( 'WC_Order_Item' );
		$wc_order_item_2
			->shouldReceive( 'get_quantity' )
			->once()
			->andReturn( $product_2_quantity );
		$wc_order_item_2
			->shouldReceive( 'get_product' )
			->once()
			->andReturn( $product_2 );
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
			->shouldReceive( 'get_shipping_total' )
			->once()
			->andReturn( $shipping_total );
		$wc_order
			->shouldReceive( 'get_items' )
			->once()
			->andReturn( [ $wc_order_item_1, $wc_order_item_2 ] );
		$wc_order
			->shouldReceive( 'get_payment_method' )
			->withNoArgs()
			->once()
			->andReturn( Nova_Poshta_Gateway_COD::ID );
		$shipping_cost = Mockery::mock( 'Nova_Poshta\Core\Shipping_Cost' );
		$shipping_cost
			->shouldReceive( 'get_products_weight' )
			->with(
				[
					[
						'quantity' => $product_1_quantity,
						'data'     => $product_1,
					],
					[
						'quantity' => $product_2_quantity,
						'data'     => $product_2,
					],
				]
			)
			->once()
			->andReturn( $weight );
		$shipping_cost
			->shouldReceive( 'get_products_volume' )
			->with(
				[
					[
						'quantity' => $product_1_quantity,
						'data'     => $product_1,
					],
					[
						'quantity' => $product_2_quantity,
						'data'     => $product_2,
					],
				]
			)
			->once()
			->andReturn( $volume );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice\Notice' );
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
		$internet_document = new Internet_Document( $api, $shipping_cost, $notice );

		$internet_document->create( $wc_order );
	}

}
