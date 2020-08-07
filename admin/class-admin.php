<?php
/**
 * Admin area
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Exception;
use Nova_Poshta\Core\API;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Core\Language;
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
	 * Plugin language.
	 *
	 * @var Language
	 */
	private $language;

	/**
	 * Admin constructor.
	 *
	 * @param API      $api      API for Nova Poshta API.
	 * @param Settings $settings Plugin settings.
	 * @param Language $language Plugin language.
	 */
	public function __construct( API $api, Settings $settings, Language $language ) {
		$this->settings = $settings;
		$this->api      = $api;
		$this->language = $language;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_setting' ] );
		add_filter( 'pre_update_option_shipping-nova-poshta-for-woocommerce', [ $this, 'validate' ], 10, 2 );
	}

	/**
	 * Enqueue styles
	 */
	public function enqueue_styles() {
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_style( 'np-notice', plugin_dir_url( __FILE__ ) . '/assets/css/notice.css', [], Main::VERSION, 'all' );
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		wp_enqueue_style( 'np-select2', plugin_dir_url( __DIR__ ) . '/front/assets/css/select2.min.css', [], Main::VERSION, 'all' );
		wp_enqueue_style( 'np-tip-tip', plugin_dir_url( __FILE__ ) . '/assets/css/tip-tip.css', [], Main::VERSION, 'all' );
		wp_enqueue_style( Main::PLUGIN_SLUG, plugin_dir_url( __FILE__ ) . '/assets/css/main.css', [ 'np-select2' ], Main::VERSION, 'all' );
		wp_enqueue_style( Main::PLUGIN_SLUG . '-front', plugin_dir_url( __DIR__ ) . '/front/assets/css/main.css', [ 'np-select2' ], Main::VERSION, 'all' );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_script(
			'np-notice',
			plugin_dir_url( __FILE__ ) . '/assets/js/notice.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		);
		wp_localize_script(
			'np-notice',
			'shipping_nova_poshta_for_woocommerce',
			[
				'url'      => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( Main::PLUGIN_SLUG ),
				'language' => $this->language->get_current_language(),
			]
		);
		if ( ! $this->is_plugin_page() ) {
			return;
		}
		wp_enqueue_script(
			'np-select2',
			plugin_dir_url( __DIR__ ) . '/front/assets/js/select2.min.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		);
		wp_enqueue_script(
			'np-select2-i18n-' . $this->language->get_current_language(),
			plugin_dir_url( __DIR__ ) . '/front/assets/js/i18n/' . $this->language->get_current_language() . '.js',
			[ 'jquery', 'np-select2' ],
			Main::VERSION,
			true
		);
		wp_enqueue_script(
			'np-tip-tip',
			plugin_dir_url( __FILE__ ) . '/assets/js/jquery.tip-tip.min.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		);
		wp_enqueue_script(
			Main::PLUGIN_SLUG,
			plugin_dir_url( __FILE__ ) . '/assets/js/main.js',
			[
				'jquery',
				'np-select2',
			],
			Main::VERSION,
			true
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
		global $current_screen, $post_type;

		return 0 === strpos( $current_screen->base, 'toplevel_page_' . Main::PLUGIN_SLUG ) || 'shop_order' === $post_type || 'product_cat' === $current_screen->taxonomy;
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
	 * View for page options
	 *
	 * @throws Exception Invalid DateTime.
	 */
	public function page_options() {
		require plugin_dir_path( __FILE__ ) . 'partials/page-options/page-options.php';
	}

	/**
	 * Validate api key
	 *
	 * @param array $value Option value.
	 *
	 * @return array
	 */
	public function validate( array $value ): array {
		if ( ! empty( $value['api_key'] ) && ! $this->api->validate( $value['api_key'] ) ) {
			add_settings_error( Main::PLUGIN_SLUG, 403, __( 'Invalid api key', 'shipping-nova-poshta-for-woocommerce' ) );
			unset( $value['api_key'] );
		}

		return is_array( $value ) ? $value : [];
	}

}
