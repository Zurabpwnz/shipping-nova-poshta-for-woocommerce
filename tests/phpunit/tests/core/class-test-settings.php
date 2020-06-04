<?php
/**
 * Settings tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_Settings
 *
 * @package Nova_Poshta\Core
 */
class Test_Settings extends Test_Case {

	/**
	 * Test get api key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_api_key() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $api_key, $settings->api_key() );
	}

	/**
	 * Test get empty api key
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_api_key() {
		when( '__' )->returnArg();
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [] );
		expect( 'get_admin_url' )
			->with( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
			->once()
			->andReturn( 'url' );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );
		$notice
			->shouldReceive( 'add' )
			->with(
				'error',
				'For the plugin to work, you must enter the API key on the <a href="url">plugin settings page</a>'
			)->
			once();

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->api_key() );
	}

	/**
	 * Test get phone
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_phone() {
		$api_key = 'api-key';
		$phone   = 'phone';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
					'phone'   => $phone,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $phone, $settings->phone() );
	}

	/**
	 * Test get empty phone
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_phone() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->phone() );
	}

	/**
	 * Test get description
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_description() {
		$api_key     = 'api-key';
		$description = 'description';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'     => $api_key,
					'description' => $description,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $description, $settings->description() );
	}

	/**
	 * Test get empty phone
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_description() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( 'Товар', $settings->description() );
	}

	/**
	 * Test get city_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_city_id() {
		$api_key = 'api-key';
		$city_id = 'city-id';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
					'city_id' => $city_id,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $city_id, $settings->city_id() );
	}

	/**
	 * Test get empty city_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_city_id() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->city_id() );
	}

	/**
	 * Test get warehouse_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_warehouse_id() {
		$api_key      = 'api-key';
		$warehouse_id = 'warehouse-id';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'      => $api_key,
					'warehouse_id' => $warehouse_id,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $warehouse_id, $settings->warehouse_id() );
	}

	/**
	 * Test get empty warehouse_id
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_warehouse_id() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '', $settings->warehouse_id() );
	}

	/**
	 * Test shipping cost enable
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_cost_enable() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'                 => $api_key,
					'is_shipping_cost_enable' => 1,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertTrue( $settings->is_shipping_cost_enable() );
	}

	/**
	 * Test shipping cost enable
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_shipping_cost_DISABLED() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn( [ 'api_key' => $api_key ] );
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertFalse( $settings->is_shipping_cost_enable() );
	}

	/**
	 * Test get default_weight_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_default_weight_formula() {
		$api_key        = 'api-key';
		$weight_formula = 'weight-formula';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'                => $api_key,
					'default_weight_formula' => $weight_formula,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $weight_formula, $settings->default_weight_formula() );
	}

	/**
	 * Test get empty default_weight_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_default_weight_formula() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '[qty] * 0.5', $settings->default_weight_formula() );
	}

	/**
	 * Test get default_width_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_default_width_formula() {
		$api_key       = 'api-key';
		$width_formula = 'width-formula';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'               => $api_key,
					'default_width_formula' => $width_formula,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $width_formula, $settings->default_width_formula() );
	}

	/**
	 * Test get empty default_width_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_default_width_formula() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '[qty] * 0.26', $settings->default_width_formula() );
	}

	/**
	 * Test get default_height_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_default_height_formula() {
		$api_key        = 'api-key';
		$height_formula = 'height-formula';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'                => $api_key,
					'default_height_formula' => $height_formula,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $height_formula, $settings->default_height_formula() );
	}

	/**
	 * Test get empty default_height_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_default_height_formula() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '[qty] * 0.1', $settings->default_height_formula() );
	}

	/**
	 * Test get default_length_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_default_length_formula() {
		$api_key        = 'api-key';
		$length_formula = 'length-formula';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key'                => $api_key,
					'default_length_formula' => $length_formula,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( $length_formula, $settings->default_length_formula() );
	}

	/**
	 * Test get empty default_length_formula
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_empty_default_length_formula() {
		$api_key = 'api-key';
		expect( 'get_option' )
			->with( Main::PLUGIN_SLUG, [] )
			->once()
			->andReturn(
				[
					'api_key' => $api_key,
				]
			);
		$notice = Mockery::mock( 'Nova_Poshta\Admin\Notice' );

		$settings = new Settings( $notice );

		$this->assertSame( '[qty] * 0.145', $settings->default_length_formula() );
	}

}
