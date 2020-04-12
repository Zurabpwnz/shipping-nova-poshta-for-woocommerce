<?php
/**
 * Admin area
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\API;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Core\Settings;

/**
 * Class Admin
 *
 * @package Nova_Poshta\Admin
 */
class Admin {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;
	/**
	 * API for Nova Poshta API
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Admin constructor.
	 *
	 * @param API      $api      API for Nova Poshta API.
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( API $api, Settings $settings ) {
		$this->settings = $settings;
		$this->api      = $api;
	}

	/**
	 * Enqueue styles
	 */
	public function styles() {
		// todo: Think on naming. Function name should include verb. enqueue_styles() looks better for me.
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		wp_enqueue_style( 'select2', plugin_dir_url( __DIR__ ) . 'front/assets/css/select2.min.css', [], Main::VERSION, 'all' );
		wp_enqueue_style( Main::PLUGIN_SLUG, plugin_dir_url( __FILE__ ) . '/assets/css/main.css', [ 'select2' ], Main::VERSION, 'all' );
	}

	/**
	 * Enqueue scripts
	 */
	public function scripts() {
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		wp_enqueue_script(
			'select2',
			plugin_dir_url( __DIR__ ) . 'front/assets/js/select2.min.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		);
		wp_enqueue_script(
			Main::PLUGIN_SLUG,
			plugin_dir_url( __DIR__ ) . 'front/assets/js/main.js',
			[
				'jquery',
				'select2',
			],
			Main::VERSION,
			true
		);
		wp_localize_script(
			Main::PLUGIN_SLUG,
			'woo_nova_poshta',
			[
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( Main::PLUGIN_SLUG ),
			]
		);
	}

	/**
	 * Register settings
	 */
	public function register_setting() {
		register_setting( Main::PLUGIN_SLUG, Main::PLUGIN_SLUG );
	}

	/**
	 * Is current page
	 *
	 * @return bool
	 */
	private function is_plugin_page(): bool {
		global $current_screen;

		return 0 === strpos( $current_screen->base, 'toplevel_page_' . Main::PLUGIN_SLUG );
	}

	/**
	 * Register page option in menu
	 */
	public function add_menu() {
		add_menu_page(
			Main::PLUGIN_NAME,
			Main::PLUGIN_NAME,
			'manage_options',
			Main::PLUGIN_SLUG,
			[
				$this,
				'page_options',
			],
			plugin_dir_url( __FILE__ ) . 'assets/img/nova-poshta.svg'
		);
	}

	/**
	 * Controller for creating invoices
	 */
	private function controller(): void {
		$nonce = filter_input( INPUT_POST, Main::PLUGIN_SLUG . '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-invoice' ) ) {
			return;
		}
		$fields = filter_input( INPUT_POST, Main::PLUGIN_SLUG, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		$this->api->internet_document(
			$fields['first_name'],
			$fields['last_name'],
			$fields['phone'],
			$fields['city_id'],
			$fields['warehouse_id'],
			$fields['price'],
			1,
			isset( $fields['backward'] ) && ! empty( $fields['redelivery'] ) ? $fields['redelivery'] : 0
		);
	}

	/**
	 * View for page options
	 */
	public function page_options() {
		$this->controller();
		// todo: why to do anything if nonce check was not passed?
		// todo: check_admin_referer() should be enough.
		require plugin_dir_path( __FILE__ ) . 'partials/page-options.php';
	}

	/**
	 * Validate api key
	 *
	 * @param array $value Option value.
	 *
	 * @return array
	 */
	public function validate( array $value ): array {
		if ( isset( $value['api_key'] ) && ! $this->api->validate( $value['api_key'] ) ) {
			add_settings_error( Main::PLUGIN_SLUG, 403, __( 'Invalid api key', 'woo-nova-poshta' ) );
		}

		return $value ?? [];
	}

}
