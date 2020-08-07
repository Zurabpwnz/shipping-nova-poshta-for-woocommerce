<?php
/**
 * Admin manage orders list table
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\stubs;
use function Brain\Monkey\Functions\expect;

/**
 * Class Test_Manage_Orders_List_Table
 *
 * @package Nova_Poshta\Admin
 */
class Test_Manage_Orders_List_Table extends Test_Case {

	/**
	 * Setup test
	 */
	public function setUp() {
		parent::setUp();
		unset( $_REQUEST ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_SERVER );
		unset( $_GET );//phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Test redirect in prepare items
	 */
	public function test_prepare_items_redirect() {
		$some_url                 = '/some-url/';
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = Mockery::mock( Manage_Orders_List_Table::class, [ $internet_document ] )->makePartial();
		$manage_orders_list_table
			->shouldReceive( 'exit' )
			->once();
		$_REQUEST['_wp_http_referer'] = 'some-referer';
		$_SERVER['REQUEST_URI']       = $some_url;
		expect( 'wp_safe_redirect' )
			->with( $some_url )
			->once();
		expect( 'remove_query_arg' )
			->with(
				[ '_wp_http_referer', '_wpnonce' ],
				$some_url
			)
			->once()
			->andReturn( $some_url );
		expect( 'wp_unslash' )
			->with(
				$some_url
			)
			->once()
			->andReturn( $some_url );

		$manage_orders_list_table->prepare_items();
	}

	/**
	 * Test prepare items with pagination, search and limit.
	 */
	public function test_prepare_items() {
		$user_id                  = 1;
		$per_page                 = 11;
		$per_page_option          = 'per_page_option';
		$total                    = 33;
		$max_page                 = 3;
		$s                        = 'search request';
		$post__in                 = [ 1, 2, 3 ];
		$query                    = (object) [
			'total'         => $total,
			'max_num_pages' => $max_page,
			'orders'        => [ 'item1', 'item2' ],
		];
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = Mockery::mock( Manage_Orders_List_Table::class, [ $internet_document ] )->makePartial();
		$manage_orders_list_table
			->shouldReceive( 'set_pagination_args' )
			->with(
				[
					'total_items' => $total,
					'total_pages' => $max_page,
					'per_page'    => $per_page,
				]
			);
		$screen       = Mockery::mock( 'WP_Screen' );
		$filter_input = FunctionMocker::replace( 'filter_input', $s );
		$screen
			->shouldReceive( 'get_option' )
			->with( 'per_page', 'option' )
			->once()
			->andReturn( $per_page_option );
		expect( 'get_user_meta' )
			->with( $user_id, $per_page_option, true )
			->once()
			->andReturn( 11 );
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_current_screen' )
			->withNoArgs()
			->once()
			->andReturn( $screen );
		expect( 'wc_get_orders' )
			->with(
				[
					'status'            => [ 'processing', 'on-hold' ],
					'type'              => 'shop_order',
					'posts_per_page'    => $per_page,
					'paginate'          => true,
					'shop_order_search' => true,
					'post__in'          => $post__in + [ 0 ],
				]
			)
			->once()
			->andReturn( $query );
		expect( 'wc_order_search' )
			->with( $s )
			->once()
			->andReturn( $post__in );
		$manage_orders_list_table->prepare_items();

		$filter_input->wasCalledWithOnce( [ INPUT_GET, 's', FILTER_SANITIZE_STRING ] );
	}

	/**
	 * Test view a cb column
	 */
	public function test_column_cb() {
		when( 'esc_attr' )->returnArg();
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_id' )
			->once()
			->andReturn( 1 );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			'<input name="ID[]" type="checkbox" value="1" />',
			$manage_orders_list_table->column_cb( $wc_order )
		);
	}

	/**
	 * Test view a ID column
	 */
	public function test_column_ID() {
		$id        = 10;
		$number    = 20;
		$full_name = 'Full Name';
		$edit_link = 'edit-link';
		stubs(
			[
				'esc_url',
				'esc_html',
				'absint',
			]
		);
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_id' )
			->once()
			->andReturn( $id );
		$wc_order
			->shouldReceive( 'get_order_number' )
			->once()
			->andReturn( $number );
		$wc_order
			->shouldReceive( 'get_formatted_shipping_full_name' )
			->once()
			->andReturn( ' ' );
		$wc_order
			->shouldReceive( 'get_formatted_billing_full_name' )
			->once()
			->andReturn( $full_name );
		expect( 'get_edit_post_link' )
			->with( $id )
			->once()
			->andReturn( $edit_link );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			sprintf(
				'<a href="%s"><strong>#%s %s</strong></a>',
				$edit_link,
				$number,
				$full_name
			),
			$manage_orders_list_table->column_ID( $wc_order )
		);
	}

	/**
	 * Test view a date column
	 */
	public function test_column_date() {
		$date_format = 'd.m.Y';
		$time_format = 'h:i:S';
		$date        = '11.11.1111 11:11:11';
		$wc_date     = Mockery::mock( 'WC_Date' );
		$wc_date
			->shouldReceive( 'date_i18n' )
			->with( $date_format . ' ' . $time_format )
			->andReturn( $date );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_date_created' )
			->once()
			->andReturn( $wc_date );
		expect( 'get_option' )
			->with( 'date_format' )
			->once()
			->andReturn( $date_format );
		expect( 'get_option' )
			->with( 'time_format' )
			->once()
			->andReturn( $time_format );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$date,
			$manage_orders_list_table->column_date( $wc_order )
		);
	}

	/**
	 * Test view a email column
	 */
	public function test_column_email() {
		when( 'esc_html' )->returnArg();
		$email    = 'email@example.com';
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_billing_email' )
			->once()
			->andReturn( $email );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$email,
			$manage_orders_list_table->column_email( $wc_order )
		);
	}

	/**
	 * Test view a phone column
	 */
	public function test_column_phone() {
		when( 'esc_html' )->returnArg();
		$phone    = '111-11-11';
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_billing_phone' )
			->once()
			->andReturn( $phone );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$phone,
			$manage_orders_list_table->column_phone( $wc_order )
		);
	}

	/**
	 * Test view a internet column without data
	 */
	public function test_EMPTY_column_internet_document() {
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [] );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertEmpty(
			$manage_orders_list_table->column_internet_document( $wc_order )
		);
	}

	/**
	 * Test view a internet column
	 */
	public function test_column_internet_document() {
		$internet_document_value = '1111 1111 1111 1111';
		when( 'esc_html' )->returnArg();
		$shipping_methods = Mockery::mock( 'WC_Shippin_Method' );
		$shipping_methods
			->shouldReceive( 'get_meta' )
			->with( 'internet_document' )
			->once()
			->andReturn( $internet_document_value );
		$wc_order = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_methods' )
			->once()
			->andReturn( [ $shipping_methods ] );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$internet_document_value,
			$manage_orders_list_table->column_internet_document( $wc_order )
		);
	}

	/**
	 * Test view a shipping total column
	 */
	public function test_column_shipping_total() {
		$shipping_total       = 11;
		$shipping_total_price = '$' . $shipping_total;
		$wc_order             = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_shipping_total' )
			->once()
			->andReturn( $shipping_total );
		expect( 'wc_price' )
			->with( $shipping_total )
			->once()
			->andReturn( $shipping_total_price );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$shipping_total_price,
			$manage_orders_list_table->column_shipping_total( $wc_order )
		);
	}

	/**
	 * Test view a total column
	 */
	public function test_column_total() {
		$total       = 111;
		$total_price = '$' . $total;
		$wc_order    = Mockery::mock( 'WC_Order' );
		$wc_order
			->shouldReceive( 'get_total' )
			->once()
			->andReturn( $total );
		expect( 'wc_price' )
			->with( $total )
			->once()
			->andReturn( $total_price );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			$total_price,
			$manage_orders_list_table->column_total( $wc_order )
		);
	}

	/**
	 * Test list of columns
	 */
	public function test_get_columns() {
		when( '__' )->returnArg();
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = new Manage_Orders_List_Table( $internet_document );

		$this->assertSame(
			[
				'cb'                => '<input type="checkbox" />',
				'ID'                => 'Order ID',
				'date'              => 'Date',
				'phone'             => 'Phone',
				'email'             => 'Email',
				'internet_document' => 'Internet Document',
				'shipping_total'    => 'Shipping Total',
				'total'             => 'Total',
			],
			$manage_orders_list_table->get_columns()
		);
	}

	/**
	 * Test display page
	 */
	public function test_display_page() {
		$user_id                = 1;
		$per_page               = 11;
		$per_page_option        = 'per_page_option';
		$total                  = 33;
		$max_page               = 3;
		$s                      = 'search request';
		$post__in               = [ 1, 2, 3 ];
		$query                  = (object) [
			'total'         => $total,
			'max_num_pages' => $max_page,
			'orders'        => [ 'item1', 'item2' ],
		];
		$nonce                  = 'nonce';
		$action                 = 'create_internet_document';
		$some_url               = '/some-url/';
		$_SERVER['REQUEST_URI'] = $some_url;
		stubs(
			[
				'__',
				'esc_attr',
				'esc_attr__',
			]
		);
		$screen        = Mockery::mock( 'WP_Screen' );
		$filter_input  = FunctionMocker::replace( 'filter_input', $s );
		$_GET['paged'] = 2;
		$_GET['p']     = 10;
		$screen
			->shouldReceive( 'get_option' )
			->with( 'per_page', 'option' )
			->once()
			->andReturn( $per_page_option );
		expect( 'get_user_meta' )
			->with( $user_id, $per_page_option, true )
			->once()
			->andReturn( 11 );
		expect( 'get_current_user_id' )
			->withNoArgs()
			->once()
			->andReturn( $user_id );
		expect( 'get_current_screen' )
			->withNoArgs()
			->once()
			->andReturn( $screen );
		expect( 'wc_get_orders' )
			->with(
				[
					'status'            => [ 'processing', 'on-hold' ],
					'type'              => 'shop_order',
					'posts_per_page'    => $per_page,
					'paginate'          => true,
					'shop_order_search' => true,
					'post__in'          => $post__in + [ 0 ],
				]
			)
			->once()
			->andReturn( $query );
		expect( 'wc_order_search' )
			->with( $s )
			->once()
			->andReturn( $post__in );
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table', [ $internet_document ] )->makePartial();
		$manage_orders_list_table
			->shouldReceive( 'set_pagination_args' )
			->with(
				[
					'total_items' => $total,
					'total_pages' => $max_page,
					'per_page'    => $per_page,
				]
			);
		$manage_orders_list_table
			->shouldReceive( 'search_box' )
			->with( 'Search', 'plugin' )
			->once();
		ob_start();
		$manage_orders_list_table->display_page();

		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test controller with not verifiend nonce
	 */
	public function test_controller_NOT_verified_nonce() {
		$_GET['ID']       = [ 1, 2, 3 ];
		$_GET['_wpnonce'] = 'nonce';
		$_GET['action']   = 'action';
		expect( 'wp_verify_nonce' )
			->withAnyArgs()
			->once()
			->andReturn( false );
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table' )->makePartial();
		$manage_orders_list_table->shouldAllowMockingProtectedMethods();
		$manage_orders_list_table->_args = [ 'plural' => 'orders' ];

		$this->run_inaccesible_method( $manage_orders_list_table, 'bulk_action_controller' );
	}

	/**
	 * Test controller
	 */
	public function test_controller() {
		$nonce                  = 'nonce';
		$action                 = 'create_internet_document';
		$some_url               = '/some-url/';
		$_GET['ID']             = [ 1, 2, 3 ];
		$_GET['_wpnonce']       = $nonce;
		$_GET['action']         = $action;
		$_SERVER['REQUEST_URI'] = $some_url;
		expect( 'wp_verify_nonce' )
			->with( $nonce, 'bulk-orders' )
			->once()
			->andReturn( true );
		expect( 'wp_safe_redirect' )
			->with( $some_url )
			->once();
		expect( 'remove_query_arg' )
			->with(
				[ '_wp_http_referer', '_wpnonce', 'ID', 'action', 'action2' ],
				$some_url
			)
			->once()
			->andReturn( $some_url );
		expect( 'wp_unslash' )
			->with( $some_url )
			->once()
			->andReturn( $some_url );
		$result = [ $nonce, 'create_internet_document', null ];
		FunctionMocker::replace(
			'filter_input',
			function () use ( $result ) {
				static $i = 0;

				return $result[ $i ++ ];
			}
		);
		$internet_document        = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table' )->makePartial();
		$manage_orders_list_table->shouldAllowMockingProtectedMethods();
		$manage_orders_list_table
			->shouldReceive( 'exit' )
			->once();
		$this->update_inaccessible_property( $manage_orders_list_table, 'internet_document', $internet_document );
		$manage_orders_list_table->_args = [ 'plural' => 'orders' ];

		$this->run_inaccesible_method( $manage_orders_list_table, 'bulk_action_controller' );
	}

	/**
	 * Test empty bulk create internet document
	 */
	public function test_EMPTY_bulk_create_internet_document() {
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table' )->makePartial();
		$manage_orders_list_table->shouldAllowMockingProtectedMethods();

		$this->run_inaccesible_method( $manage_orders_list_table, 'bulk_create_internet_document' );
	}

	/**
	 * Test bulk create internet document
	 */
	public function test_bulk_create_internet_document() {
		$ids               = [ 1, 2, 3 ];
		$wc_order1         = Mockery::mock( 'WC_Order' );
		$wc_order2         = Mockery::mock( 'WC_Order' );
		$wc_order3         = Mockery::mock( 'WC_Order' );
		$filter_input      = FunctionMocker::replace( 'filter_input', $ids );
		$internet_document = Mockery::mock( 'Nova_Poshta\Core\Internet_Document' );
		$internet_document
			->shouldReceive( 'create' )
			->with( Mockery::anyOf( $wc_order1, $wc_order2, $wc_order3 ) )
			->times( 3 );
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table' )->makePartial();
		$manage_orders_list_table->shouldAllowMockingProtectedMethods();
		expect( 'wc_get_order' )
			->with( $ids[0] )
			->once()
			->andReturn( $wc_order1 );
		expect( 'wc_get_order' )
			->with( $ids[1] )
			->once()
			->andReturn( $wc_order2 );
		expect( 'wc_get_order' )
			->with( $ids[2] )
			->once()
			->andReturn( $wc_order3 );
		$this->update_inaccessible_property( $manage_orders_list_table, 'internet_document', $internet_document );

		$this->run_inaccesible_method( $manage_orders_list_table, 'bulk_create_internet_document' );

		$filter_input->wasCalledWithOnce( [ INPUT_GET, 'ID', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY ] );
	}

	/**
	 * Test get bulk action list
	 */
	public function test_get_bulk_actions() {
		when( 'esc_html__' )->returnArg();
		$manage_orders_list_table = Mockery::mock( 'Nova_Poshta\Admin\Manage_Orders_List_Table' )->makePartial();
		$manage_orders_list_table->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			[ 'create_internet_document' => 'Create internet documents' ],
			$this->run_inaccesible_method( $manage_orders_list_table, 'get_bulk_actions' )
		);
	}

}
