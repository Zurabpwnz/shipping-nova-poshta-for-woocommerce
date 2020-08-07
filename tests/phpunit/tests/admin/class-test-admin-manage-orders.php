<?php
/**
 * Admin manage orders area
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Tests\Test_Case;
use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Admin_Manage_Orders
 *
 * @package Nova_Poshta\Admin
 */
class Test_Admin_Manage_Orders extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );
		$admin_manage_orders->hooks();

		$this->assertTrue( has_action( 'admin_menu', [ $admin_manage_orders, 'add_menu' ] ) );
		$this->assertTrue( has_action( 'load-nova-poshta_page_' . Admin_Manage_Orders::SCREEN_ID, [ $admin_manage_orders, 'register_screen_options' ] ) );
		$this->assertTrue( has_filter( 'set-screen-option', [ $admin_manage_orders, 'save_pagination_option' ] ) );
		$this->assertTrue( has_filter( 'set_screen_option_internet_document_page_per_page', [ $admin_manage_orders, 'save_pagination_option' ] ) );
	}

	/**
	 * Test adding menu
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_add_menu() {
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );
		when( '__' )->returnArg();
		expect( 'add_submenu_page' )
			->with(
				Main::PLUGIN_SLUG,
				'Manage orders',
				'Manage orders',
				'manage_options',
				Admin_Manage_Orders::SCREEN_ID,
				[
					$admin_manage_orders,
					'view',
				]
			)->
			once();

		$admin_manage_orders->add_menu();
	}

	/**
	 * Test register screen options
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_register_screen_options() {
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );
		when( '__' )->returnArg();
		expect( 'add_screen_option' )
			->with(
				'per_page',
				[
					'label'   => 'Show on page',
					'default' => 10,
					'option'  => 'internet_document_page_per_page',
				]
			)
			->once();

		$admin_manage_orders->register_screen_options();
	}

	/**
	 * Test don't save pagination option.
	 */
	public function test_do_NOT_save_pagination_option() {
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );

		$this->assertFalse( $admin_manage_orders->save_pagination_option( false, 'some-options', 111 ) );
	}

	/**
	 * Test save pagination option.
	 */
	public function test_save_pagination_option() {
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );

		$this->assertSame( 111, $admin_manage_orders->save_pagination_option( false, 'internet_document_page_per_page', 111 ) );
	}

	/**
	 * Test register screen options
	 *
	 * @throws \ReflectionException Invalid property name.
	 */
	public function test_view() {
		$list_table = Mockery::mock( '\Manage_Orders_List_Table' );
		$list_table
			->shouldReceive( 'display_page' )
			->once();
		$internet_document   = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$admin_manage_orders = new Admin_Manage_Orders( $internet_document );
		$this->update_inaccessible_property( $admin_manage_orders, 'view', $list_table );

		$admin_manage_orders->view();
	}

}
