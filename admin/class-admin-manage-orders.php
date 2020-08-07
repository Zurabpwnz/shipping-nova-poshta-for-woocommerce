<?php
/**
 * Admin page manage options
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Main;
use Nova_Poshta\Core\Internet_Document;

/**
 * Class Admin
 *
 * @package Nova_Poshta\Admin_Internet_Document
 */
class Admin_Manage_Orders {

	/**
	 * Screen ID.
	 */
	const SCREEN_ID = Main::PLUGIN_SLUG . '-internet-document';
	/**
	 * View
	 *
	 * @var Manage_Orders_List_Table|null
	 */
	private $view;
	/**
	 * Internet_Document
	 *
	 * @var Internet_Document
	 */
	private $internet_document;

	/**
	 * Admin_Manage_Orders constructor.
	 *
	 * @param Internet_Document $internet_document Internet_Document.
	 */
	public function __construct( Internet_Document $internet_document ) {
		$this->internet_document = $internet_document;
	}

	/**
	 * Add hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'load-nova-poshta_page_' . self::SCREEN_ID, [ $this, 'register_screen_options' ] );
		add_filter( 'set-screen-option', [ $this, 'save_pagination_option' ], 10, 3 );
		add_filter( 'set_screen_option_internet_document_page_per_page', [ $this, 'save_pagination_option' ], 10, 3 );
	}

	/**
	 * Register menu
	 */
	public function add_menu() {
		add_submenu_page(
			Main::PLUGIN_SLUG,
			__( 'Manage orders', 'shipping-nova-poshta-for-woocommerce' ),
			__( 'Manage orders', 'shipping-nova-poshta-for-woocommerce' ),
			'manage_options',
			self::SCREEN_ID,
			[
				$this,
				'view',
			]
		);
	}

	/**
	 * Register screen
	 */
	public function register_screen_options() {
		add_screen_option(
			'per_page',
			[
				'label'   => __( 'Show on page', 'shipping-nova-poshta-for-woocommerce' ),
				'default' => 10,
				'option'  => 'internet_document_page_per_page',
			]
		);
		$this->view = new Manage_Orders_List_Table( $this->internet_document );
	}

	/**
	 * Save pagination option
	 *
	 * @param mixed  $status Default value.
	 * @param string $option Option name.
	 * @param mixed  $value  Value.
	 *
	 * @return mixed
	 */
	public function save_pagination_option( $status, string $option, $value ) {
		return 'internet_document_page_per_page' === $option ? absint( $value ) : $status;
	}

	/**
	 * Show page
	 */
	public function view() {
		$this->view->display_page();
	}

}
