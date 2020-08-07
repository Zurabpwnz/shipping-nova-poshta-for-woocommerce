<?php
/**
 * Manage orders list table
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use WC_Order;
use Exception;
use WP_List_Table;
use Nova_Poshta\Core\Internet_Document;

/**
 * Class Internet_Document_List_Table
 */
class Manage_Orders_List_Table extends WP_List_Table {

	/**
	 * Internet_Document
	 *
	 * @var Internet_Document
	 */
	protected $internet_document;

	/**
	 * Internet_Document_List_Table constructor.
	 *
	 * @param Internet_Document $internet_document Internet_Document.
	 */
	public function __construct( Internet_Document $internet_document ) {
		$this->internet_document = $internet_document;
		parent::__construct(
			[
				'singular' => 'order',
				'plural'   => 'orders',
				'ajax'     => false,
			]
		);
		$this->bulk_action_controller();
	}

	/**
	 * Get search request
	 *
	 * @return string
	 */
	private function get_request_search_query(): string {
		return filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING ) ?? '';
	}

	/**
	 * Prepare items
	 */
	public function prepare_items() {
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) { // phpcs:ignore
			wp_safe_redirect(
				remove_query_arg(
					[ '_wp_http_referer', '_wpnonce' ],
					wp_unslash( $_SERVER['REQUEST_URI'] ) // phpcs:ignore
				)
			);

			return $this->exit();
		}

		$per_page = $this->get_per_page();
		$args     = [
			'status'         => [ 'processing', 'on-hold' ],
			'type'           => 'shop_order',
			'posts_per_page' => $per_page,
			'paginate'       => true,
		];

		$search = $this->get_request_search_query();
		if ( $search ) {
			$post_ids                  = wc_order_search( $search );
			$args['shop_order_search'] = true;
			$args['post__in']          = array_merge( $post_ids, array( 0 ) );
		}
		$query       = wc_get_orders(
			apply_filters( 'shipping_nova_poshta_for_woocommerce_manage_orders_args', $args )
		);
		$this->items = $query->orders;

		$this->set_pagination_args(
			[
				'total_items' => $query->total,
				'total_pages' => $query->max_num_pages,
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * Exit PHP.
	 *
	 * @codeCoverageIgnore
	 */
	public function exit() {
		exit;
	}

	/**
	 * Get per page argument
	 *
	 * @return int
	 */
	private function get_per_page(): int {
		$per_page = get_user_meta(
			get_current_user_id(),
			get_current_screen()->get_option( 'per_page', 'option' ),
			true
		);

		return $per_page ? absint( $per_page ) : 10;
	}

	/**
	 * Renders the checkbox for each row, this is the first column and it is named ID regardless
	 * of how the primary key is named (to keep the code simpler). The bulk actions will do the proper
	 * name transformation though using `$this->ID`.
	 *
	 * @param WC_Order $row Order.
	 *
	 * @return string
	 */
	public function column_cb( $row ) {
		return '<input name="ID[]" type="checkbox" value="' . esc_attr( $row->get_id() ) . '" />';
	}

	/**
	 * Get order name.
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_ID( WC_Order $row ): string {
		return sprintf(
			'<a href="%s"><strong>#%s %s</strong></a>',
			esc_url( get_edit_post_link( $row->get_id() ) ),
			absint( $row->get_order_number() ),
			esc_html(
				strlen( $row->get_formatted_shipping_full_name() ) > 1 ? $row->get_formatted_shipping_full_name() : $row->get_formatted_billing_full_name()
			)
		);
	}

	/**
	 * Get order date
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_date( WC_Order $row ): string {
		return $row->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
	}

	/**
	 * Get order phone
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_phone( WC_Order $row ): string {
		return esc_html( $row->get_billing_phone() );
	}

	/**
	 * Get order email
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_email( WC_Order $row ): string {
		return esc_html( $row->get_billing_email() );
	}

	/**
	 * Get order internet document.
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_internet_document( WC_Order $row ): string {
		$methods = $row->get_shipping_methods();
		if ( ! $methods ) {
			return '';
		}
		$method = array_shift( $methods );

		return esc_html( $method->get_meta( 'internet_document' ) );
	}

	/**
	 * Get order shipping price
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_shipping_total( WC_Order $row ): string {
		return wc_price( $row->get_shipping_total() );
	}

	/**
	 * Get order total price
	 *
	 * @param WC_Order $row WooCommerce order.
	 *
	 * @return string
	 */
	public function column_total( WC_Order $row ): string {
		return wc_price( $row->get_total() );
	}

	/**
	 * Display the table heading and search query, if any
	 */
	protected function display_header() {
		echo '<h1 class="wp-heading-inline">' . esc_attr__( 'Active orders', 'shipping-nova-poshta-for-woocommerce' ) . '</h1>';
		if ( $this->get_request_search_query() ) {
			/* translators: %s: search query */
			echo '<span class="subtitle">' . esc_attr( sprintf( __( 'Search results for "%s"', 'shipping-nova-poshta-for-woocommerce' ), $this->get_request_search_query() ) ) . '</span>';
		}
		echo '<hr class="wp-header-end">';
	}

	/**
	 * Render admin page
	 */
	public function display_page() {
		echo '<div class="wrap">';
		$this->display_header();
		$this->prepare_items();
		$this->display_table();
		echo '</div>';
	}

	/**
	 * Renders the table list, we override the original class to render the table inside a form
	 * and to render any needed HTML (like the search box). By doing so the callee of a function can simple
	 * forget about any extra HTML.
	 */
	protected function display_table() {
		echo '<form id="' . esc_attr( $this->_args['plural'] ) . '-filter" method="get">';
		foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '_' === $key[0] || 'paged' === $key || 'ID' === $key || 'action2' === $key ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		$this->search_box( __( 'Search', 'shipping-nova-poshta-for-woocommerce' ), 'plugin' );
		parent::display();
		echo '</form>';
	}

	/**
	 * Get list of bulk actions
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return [
			'create_internet_document' => esc_html__( 'Create internet documents', 'shipping-nova-poshta-for-woocommerce' ),
		];
	}

	/**
	 * Bulk action controller
	 */
	protected function bulk_action_controller() {
		if ( empty( $_GET['_wpnonce'] ) || empty( $_GET['action'] ) || empty( $_GET['ID'] ) ) {
			return;
		}

		$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$method = 'bulk_' . $action;
		if ( method_exists( $this, $method ) ) {
			$this->{$method}();
		}

		wp_safe_redirect(
			remove_query_arg(
				[ '_wp_http_referer', '_wpnonce', 'ID', 'action', 'action2' ],
				wp_unslash( $_SERVER['REQUEST_URI'] ) // phpcs:ignore
			)
		);
		return $this->exit();
	}

	/**
	 * Bul action for creating internet documents
	 *
	 * @throws Exception Invalid DateTime.
	 */
	protected function bulk_create_internet_document() {
		$ids = filter_input( INPUT_GET, 'ID', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		if ( ! $ids ) {
			return;
		}
		foreach ( $ids as $id ) {
			$this->internet_document->create( wc_get_order( $id ) );
		}
	}

	/**
	 * Get a list of columns. The format is:
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'                => '<input type="checkbox" />',
			'ID'                => __( 'Order ID', 'shipping-nova-poshta-for-woocommerce' ),
			'date'              => __( 'Date', 'shipping-nova-poshta-for-woocommerce' ),
			'phone'             => __( 'Phone', 'shipping-nova-poshta-for-woocommerce' ),
			'email'             => __( 'Email', 'shipping-nova-poshta-for-woocommerce' ),
			'internet_document' => __( 'Internet Document', 'shipping-nova-poshta-for-woocommerce' ),
			'shipping_total'    => __( 'Shipping Total', 'shipping-nova-poshta-for-woocommerce' ),
			'total'             => __( 'Total', 'shipping-nova-poshta-for-woocommerce' ),
		];
	}

}
