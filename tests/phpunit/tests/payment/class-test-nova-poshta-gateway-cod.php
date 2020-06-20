<?php
/**
 * Test COD gateway
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use Nova_Poshta_Gateway_COD;
use ReflectionException;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_Shipping
 *
 * @package Nova_Poshta\Core
 */
class Test_Nova_Poshta_Gateway_COD extends Test_Case {

	/**
	 * Test init form fields
	 */
	public function test_init_form_fields() {
		when( '__' )->returnArg();
		$cod = new Nova_Poshta_Gateway_COD();

		$cod->init_form_fields();

		$this->assertSame(
			[
				'enabled'      => [
					'title'       => 'Enable/Disable',
					'label'       => 'Enable cash on delivery',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				],
				'title'        => [
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'Payment method description that the customer will see on your checkout.',
					'default'     => 'Cash on delivery',
					'desc_tip'    => true,
				],
				'prepayment'   => [
					'title'       => 'Prepayment',
					'type'        => 'text',
					'description' => 'Formula cost calculation. The numbers are indicated in current currency. You can use the [qty] shortcode to indicate the number of products. Leave a empty if you work without prepayment.',
					'default'     => '100 + ( [qty] - 1 ) * 20',
					'desc_tip'    => true,
				],
				'description'  => [
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'Description of the payment method that the customer will see on the checkout page. You can use the shortcode [prepayment] to write the price of prepayment and [rest] shortcode to write the rest of the amount.',
					'default'     => 'Pay [prepayment] prepayment and [rest] with cash upon delivery.',
					'desc_tip'    => true,
				],
				'instructions' => [
					'title'       => 'Instructions',
					'type'        => 'textarea',
					'description' => 'Instructions that will be added to the thank you page. You can use the shortcode [prepayment] to write the price of prepayment and [rest] shortcode to write the rest of the amount.',
					'default'     => 'Pay [prepayment] prepayment and [rest] with cash upon delivery.',
					'desc_tip'    => true,
				],
			],
			$cod->form_fields
		);
	}

	/**
	 * Test don't show description on thank you page.
	 */
	public function test_DONT_show_on_thankyou_page() {
		when( '__' )->returnArg();
		$cod = new Nova_Poshta_Gateway_COD();
		$cod->thankyou_page();
	}

	/**
	 * Test show description on thank you page.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 * @throws ReflectionException Invalid property.
	 */
	public function test_thankyou_page() {
		when( '__' )->returnArg();
		$cod          = new Nova_Poshta_Gateway_COD();
		$instructions = 'Instructions';
		$this->update_inaccessible_property( $cod, 'instructions', $instructions );
		expect( 'wp_kses_post' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );
		expect( 'wpautop' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );
		expect( 'wptexturize' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );

		ob_start();
		$cod->thankyou_page();

		$this->assertSame( $instructions, ob_get_clean() );
	}

	/**
	 * Test text when sending instruction.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 * @throws ReflectionException Invalid property.
	 */
	public function test_send_email_instructions() {
		when( '__' )->returnArg();
		$cod          = new Nova_Poshta_Gateway_COD();
		$instructions = 'Instructions';
		$this->update_inaccessible_property( $cod, 'instructions', $instructions );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_payment_method' )
			->withNoArgs()
			->once()
			->andReturn( Nova_Poshta_Gateway_COD::ID );
		expect( 'wp_kses_post' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );
		expect( 'wpautop' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );
		expect( 'wptexturize' )
			->with( $instructions )
			->once()
			->andReturn( $instructions );

		ob_start();
		$cod->email_instructions( $wc_order, false );

		$this->assertSame( $instructions, ob_get_clean() );
	}

	/**
	 * Test don't show empty description.
	 *
	 * @throws ReflectionException Invalid property.
	 */
	public function test_DONT_show_payment_fields() {
		when( '__' )->returnArg();
		$cod = new Nova_Poshta_Gateway_COD();
		$this->update_inaccessible_property( $cod, 'description', '' );
		$cod->payment_fields();
	}

	/**
	 * Test don't show empty description.
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 * @throws ReflectionException Invalid property.
	 */
	public function test_DONT_prepare_payment_fields() {
		when( '__' )->returnArg();
		$description = 'Description with [prepayment] and [rest]';
		$prepayment  = 100;

		$cod = new Nova_Poshta_Gateway_COD();
		$this->update_inaccessible_property( $cod, 'description', $description );
		$this->update_inaccessible_property( $cod, 'prepayment', $prepayment );
		$wc = (object) [
			'cart' => null,
		];
		expect( 'WC' )
			->withNoArgs()
			->once()
			->andReturn( $wc );
		expect( 'wp_kses_post' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wpautop' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wptexturize' )
			->with( $description )
			->once()
			->andReturn( $description );

		ob_start();
		$cod->payment_fields();

		$this->assertSame( $description, ob_get_clean() );
	}

	/**
	 * Test show empty description with replaced shortcodes.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws ExpectationArgsRequired Invalid arguments.
	 * @throws ReflectionException Invalid property.
	 */
	public function test_payment_fields() {
		when( '__' )->returnArg();
		$description          = 'Description with [prepayment] and [rest]';
		$prepayment           = 100;
		$total                = 700;
		$quantity             = 2;
		$diff                 = $total - $prepayment;
		$prepared_description = sprintf(
			'Description with %s and %s',
			$prepayment,
			$diff
		);
		$cart                 = Mockery::mock( 'WC_Cart' );
		$cart
			->shouldReceive( 'get_cart_item_quantities' )
			->withNoArgs()
			->once()
			->andReturn( [ $quantity ] );
		$cart
			->shouldReceive( 'get_total' )
			->with( 'no-view' )
			->once()
			->andReturn( $total );
		$calculator = Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( $prepayment, $quantity )
			->once()
			->andReturn( $prepayment );
		$wc = (object) [
			'cart' => $cart,
		];
		expect( 'WC' )
			->withNoArgs()
			->times( 3 )
			->andReturn( $wc );
		expect( 'wp_kses_post' )
			->with( $prepared_description )
			->once()
			->andReturn( $prepared_description );
		expect( 'wpautop' )
			->with( $prepared_description )
			->once()
			->andReturn( $prepared_description );
		expect( 'wptexturize' )
			->with( $prepared_description )
			->once()
			->andReturn( $prepared_description );
		expect( 'wc_price' )
			->with( $prepayment )
			->once()
			->andReturn( $prepayment );
		expect( 'wc_price' )
			->with( $diff )
			->once()
			->andReturn( $diff );
		$cod = new Nova_Poshta_Gateway_COD();
		$this->update_inaccessible_property( $cod, 'description', $description );
		$this->update_inaccessible_property( $cod, 'prepayment', $prepayment );

		ob_start();
		$cod->payment_fields();

		$this->assertSame( $prepared_description, ob_get_clean() );
	}

	/**
	 * Test show empty description with DONT replaced shortcodes.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws ExpectationArgsRequired Invalid arguments.
	 * @throws ReflectionException Invalid property.
	 */
	public function test_payment_fields_with_INVALID_rest_price() {
		when( '__' )->returnArg();
		$description = 'Description with [prepayment] and [rest]';
		$prepayment  = 100;
		$total       = 50;
		$quantity    = 2;
		$cart        = Mockery::mock( 'WC_Cart' );
		$cart
			->shouldReceive( 'get_cart_item_quantities' )
			->withNoArgs()
			->once()
			->andReturn( [ $quantity ] );
		$cart
			->shouldReceive( 'get_total' )
			->with( 'no-view' )
			->once()
			->andReturn( $total );
		$calculator = Mockery::mock( 'overload:Nova_Poshta\Core\Calculator' );
		$calculator
			->shouldReceive( 'result' )
			->with( $prepayment, $quantity )
			->once()
			->andReturn( $prepayment );
		$wc = (object) [
			'cart' => $cart,
		];
		expect( 'WC' )
			->withNoArgs()
			->times( 3 )
			->andReturn( $wc );
		expect( 'wp_kses_post' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wpautop' )
			->with( $description )
			->once()
			->andReturn( $description );
		expect( 'wptexturize' )
			->with( $description )
			->once()
			->andReturn( $description );
		$cod = new Nova_Poshta_Gateway_COD();
		$this->update_inaccessible_property( $cod, 'description', $description );
		$this->update_inaccessible_property( $cod, 'prepayment', $prepayment );

		ob_start();
		$cod->payment_fields();

		$this->assertSame( $description, ob_get_clean() );
	}

}
