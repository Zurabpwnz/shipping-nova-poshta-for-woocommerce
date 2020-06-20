<?php

use Nova_Poshta\Core\Shipping;

class Krya extends WP_List_Table {

	public function prepare_items() {
		$this->_column_headers = [
			[
				'cb'                => '<input type="checkbox" />',
				'ID'                => __( 'Order ID', 'shipping-nova-poshta-for-woocommerce' ),
				'date'              => __( 'Date', 'shipping-nova-poshta-for-woocommerce' ),
				'phone'             => __( 'Phone', 'shipping-nova-poshta-for-woocommerce' ),
				'email'             => __( 'Email', 'shipping-nova-poshta-for-woocommerce' ),
				'internet_document' => __( 'Internet Document', 'shipping-nova-poshta-for-woocommerce' ),
				'shipping_total'    => __( 'Shipping Total', 'shipping-nova-poshta-for-woocommerce' ),
				'total'             => __( 'Total', 'shipping-nova-poshta-for-woocommerce' ),
			],
		];
		add_filter( 'posts_join', function ( $join, $query ) {
			if ( ! $query->get( 'shipping_method' ) ) {
				return $join;
			}
			global $wpdb;
			$join .= ' LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_items as woi ON wp_posts.ID = woi.order_id';
			$join .= ' LEFT JOIN ' . $wpdb->order_itemmeta . ' as woim ON woi.order_item_id = woim.order_item_id';

			return $join;
		}, 10, 2 );
		add_filter( 'posts_where', function ( $where, WP_Query $query ) {
			if ( ! $query->get( 'shipping_method' ) ) {
				return $where;
			}
			global $wpdb;
			$where .= ' AND woi.order_item_type = "shipping"';
			$where .= ' AND woim.meta_key = "method_id"';
			$where .= $wpdb->prepare( ' AND woim.meta_value = %s', $query->get( 'shipping_method' ) );

			return $where;
		}, 10, 2 );

		$query       = new WC_Order_Query(
			[
				'status'          => 'processing',
				'type'            => 'shop_order',
				'shipping_method' => Shipping::METHOD_NAME,
			]
		);
		$this->items = $query->get_orders();
	}

	public function column_ID( WC_Order $row ) {
		return sprintf(
			'<a href="%s"><strong>#%s %s</strong></a>',
			get_edit_post_link( $row->get_id() ),
			$row->get_order_number(),
			$row->get_formatted_shipping_full_name()
		);
	}

	public function column_date( WC_Order $row ) {
		return $row->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
	}

	public function column_phone( WC_Order $row ) {
		return $row->get_billing_phone();
	}

	public function column_email( WC_Order $row ) {
		return $row->get_billing_email();
	}

	public function column_internet_document( WC_Order $row ) {
		$methods = $row->get_shipping_methods();
		$method  = array_shift( $methods );

		return $method->get_meta( 'internet_document' );
	}

	public function column_shipping_total( WC_Order $row ) {
		return wc_price( $row->get_shipping_total() );
	}

	public function column_total( WC_Order $row ) {
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

	public function display_page() {
		$this->display_header();
		$this->prepare_items();
		$this->display_table();
	}

	/**
	 * Renders the table list, we override the original class to render the table inside a form
	 * and to render any needed HTML (like the search box). By doing so the callee of a function can simple
	 * forget about any extra HTML.
	 */
	protected function display_table() {
		echo '<form id="' . esc_attr( $this->_args['plural'] ) . '-filter" method="get">';
		foreach ( $_GET as $key => $value ) {
			if ( '_' === $key[0] || 'paged' === $key ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		echo $this->search_box( __( 'Search', 'shipping-nova-poshta-for-woocommerce' ), 'plugin' ); // WPCS: XSS OK
		parent::display();
		echo '</form>';
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		echo '<div class="alignleft actions">';

//		$default = ! empty( $_GET['filter_by'][ $id ] ) ? $_GET['filter_by'][ $id ] : '';
		echo '<select name="filter_by[' . esc_attr( $id ) . ']" class="first" id="filter-by-' . esc_attr( $id ) . '">';
		$options = [
			'krya' => 'Krya',
		];
		foreach ( $options as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . esc_html( $value == $default ? 'selected' : '' ) . '>'
			     . esc_html( $label )
			     . '</option>';
		}

		echo '</select>';

		submit_button( esc_html__( 'Filter', 'woocommerce' ), '', 'filter_action', false, [ 'id' => 'post-query-submit' ] );
		echo '</div>';
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

	public function get_columns() {
		return array_merge(
			[ 'cb' => '<input type="checkbox" />' ],
			$this->_column_headers
		);
	}

}

( new Krya() )->display_page();
