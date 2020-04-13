<?php
/**
 * Nova_Poshta_Shipping_Method tests
 *
 * @package   Woo-Nova-Poshta
 */

use Nova_Poshta\Tests\Test_Case;

/**
 * Class Test_Thank_You
 *
 * @package Nova_Poshta\Shipping
 */
class Test_Nova_Poshta_Shipping_Method extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test___construct() {
		$nova_poshta_shipping_method = new Nova_Poshta_Shipping_Method();

		$this->assertSame( 'woo_nova_poshta', $nova_poshta_shipping_method->id );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_description );
		$this->assertTrue( $nova_poshta_shipping_method->enabled );
		$this->assertSame(
			[
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			],
			$nova_poshta_shipping_method->supports
		);
		$this->assertSame(
			[
				'title' => [
					'title'   => 'Method header',
					'type'    => 'text',
					'default' => 'Nova Poshta delivery',
				],
			],
			$nova_poshta_shipping_method->instance_form_fields
		);
	}

	/**
	 * Test save action
	 */
	public function test_save_action() {
		$stub     = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id = 'woo_nova_poshta';
		WP_Mock::expectActionAdded(
			'woocommerce_update_options_shipping_woo_nova_poshta',
			[
				$stub,
				'process_admin_options',
			]
		);

		$stub->init();
	}

}
