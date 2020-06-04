<?php
/**
 * Thank you page tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Thank_You
 *
 * @package Nova_Poshta\Core
 */
class Test_Thank_You extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$api       = Mockery::mock( 'Nova_Poshta\Core\API' );
		$thank_you = new Thank_You( $api );

		$thank_you->hooks();

		$this->assertTrue( has_filter( 'woocommerce_get_order_item_totals', [ $thank_you, 'shipping' ] ) );
	}

	/**
	 * Test Thank you page without shipping
	 */
	public function test_thank_you_page_without_shipping() {
		$api   = Mockery::mock( 'Nova_Poshta\Core\API' );
		$order = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_shipping_methods' )
			->once();
		$thank_you = new Thank_You( $api );

		$thank_you->shipping( [], $order );
	}

	/**
	 * Test thank you page with other shipping
	 */
	public function test_thank_you_page_with_other_shipping() {
		$api             = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_method = Mockery::mock( 'WC_Shipping_Method' );
		$shipping_method
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'other-shipping' );
		$order = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $shipping_method ] );
		$thank_you = new Thank_You( $api );

		$thank_you->shipping( [], $order );
	}

	/**
	 * Test thank you page with shipping
	 */
	public function test_thank_you_page_with_shipping_incorrect_data() {
		$api             = Mockery::mock( 'Nova_Poshta\Core\API' );
		$shipping_method = Mockery::mock( 'WC_Shipping_Method' );
		$shipping_method
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once();
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once();
		$order = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $shipping_method ] );
		$thank_you = new Thank_You( $api );

		$thank_you->shipping( [ 'shipping' => [ 'value' => '' ] ], $order );
	}

	/**
	 * Test thank you page with shipping
	 */
	public function test_thank_you_page_with_shipping_without_internet_document() {
		$city_id        = 'city-id';
		$city_name      = 'City Name';
		$warehouse_id   = 'warehouse-id';
		$warehouse_name = 'Warehouse Name';
		$api            = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $city_name );
		$api
			->shouldReceive( 'warehouse' )
			->withArgs( [ $warehouse_id ] )
			->once()
			->andReturn( $warehouse_name );
		$shipping_method = Mockery::mock( 'WC_Shipping_Method' );
		$shipping_method
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once()
			->andReturn( $city_id );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once()
			->andReturn( $warehouse_id );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'internet_document' ] )
			->once()
			->andReturn( false );
		$order = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $shipping_method ] );
		$thank_you = new Thank_You( $api );

		$this->assertSame(
			[ 'shipping' => [ 'value' => sprintf( '<br>%s<br>%s', $city_name, $warehouse_name ) ] ],
			$thank_you->shipping( [ 'shipping' => [ 'value' => '' ] ], $order )
		);
	}

	/**
	 * Test thank you page with shipping
	 */
	public function test_thank_you_page_with_shipping_with_internet_document() {
		$city_id           = 'city-id';
		$city_name         = 'City Name';
		$warehouse_id      = 'warehouse-id';
		$warehouse_name    = 'Warehouse Name';
		$internet_document = '1234 5678 9012 3456';
		$api               = Mockery::mock( 'Nova_Poshta\Core\API' );
		$api
			->shouldReceive( 'city' )
			->withArgs( [ $city_id ] )
			->once()
			->andReturn( $city_name );
		$api
			->shouldReceive( 'warehouse' )
			->withArgs( [ $warehouse_id ] )
			->once()
			->andReturn( $warehouse_name );
		$shipping_method = Mockery::mock( 'WC_Shipping_Method' );
		$shipping_method
			->shouldReceive( 'get_method_id' )
			->once()
			->andReturn( 'shipping_nova_poshta_for_woocommerce' );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'city_id' ] )
			->once()
			->andReturn( $city_id );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'warehouse_id' ] )
			->once()
			->andReturn( $warehouse_id );
		$shipping_method
			->shouldReceive( 'get_meta' )
			->withArgs( [ 'internet_document' ] )
			->once()
			->andReturn( $internet_document );
		$order = Mockery::mock( 'WC_Order' );
		$order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $shipping_method ] );
		$thank_you = new Thank_You( $api );

		$this->assertSame(
			[ 'shipping' => [ 'value' => sprintf( '<br>%s<br>%s<br>%s', $city_name, $warehouse_name, $internet_document ) ] ],
			$thank_you->shipping( [ 'shipping' => [ 'value' => '' ] ], $order )
		);
	}

}
