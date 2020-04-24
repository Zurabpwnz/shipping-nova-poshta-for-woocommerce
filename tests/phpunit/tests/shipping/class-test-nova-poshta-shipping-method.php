<?php
/**
 * Nova_Poshta_Shipping_Method tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
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

		$this->assertSame( 'shipping_nova_poshta_for_woocommerce', $nova_poshta_shipping_method->id );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_title );
		$this->assertSame( 'Nova Poshta delivery', $nova_poshta_shipping_method->method_description );
		$this->assertSame( 'yes', $nova_poshta_shipping_method->enabled );
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
		$stub->id = 'shipping_nova_poshta_for_woocommerce';
		WP_Mock::expectActionAdded(
			'woocommerce_update_options_shipping_shipping_nova_poshta_for_woocommerce',
			[
				$stub,
				'process_admin_options',
			]
		);

		$stub->init();
	}

	/**
	 * Test calculate shipping
	 */
	public function test_calculate_shipping() {
		$stub        = Mockery::mock( 'Nova_Poshta_Shipping_Method' )->makePartial();
		$stub->id    = 'shipping_nova_poshta_for_woocommerce';
		$stub->title = 'shipping_nova_poshta_for_woocommerce';
		$stub
			->shouldReceive( 'add_rate' )
			->once()
			->with(
				[
					'id'       => $stub->id,
					'label'    => $stub->title,
					'cost'     => '0',
					'calc_tax' => 'per_item',
				]
			);

		$stub->calculate_shipping();
	}

}
