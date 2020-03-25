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
	private $options;
	/**
	 * API for Nova Poshta API
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Admin constructor.
	 *
	 * @param API $api API for Nova Poshta API.
	 */
	public function __construct( API $api ) {
		$this->options = get_option( Main::PLUGIN_SLUG, [] );
		$this->api     = $api;
	}

	/**
	 * Enqueue styles
	 */
	public function styles() {
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
			]
		);
	}

	/**
	 * View for page options
	 */
	public function page_options() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/page-options.php';
	}

	/**
	 * Show notices
	 */
	public function notices() {
		if ( ! empty( $this->options['api_key'] ) ) {
			return;
		}
		$message = sprintf(
			'Для работы плагина неоходимо ввести API ключ на <a href="%s">странице настроек плагина</a>',
			get_admin_url( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
		);
		$type    = 'error';
		require_once plugin_dir_path( __FILE__ ) . 'partials/notice.php';
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
			add_settings_error( Main::PLUGIN_SLUG, '403', __( 'Invalid api key', 'woo-nova-poshta' ) );
		}

		return $value ?? [];
	}

}
