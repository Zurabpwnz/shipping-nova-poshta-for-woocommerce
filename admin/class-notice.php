<?php
/**
 * Admin area notices
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Main;
use Nova_Poshta\Core\Settings;
use Nova_Poshta\Core\Shipping;

/**
 * Class Admin
 *
 * @package Nova_Poshta\Admin
 */
class Notice {

	/**
	 * Plugin settings
	 *
	 * @var Settings
	 */
	private $settings;
	/**
	 * Shipping method
	 *
	 * @var Shipping
	 */
	private $shipping;

	/**
	 * Notice constructor.
	 *
	 * @param Settings $settings Plugin settings.
	 * @param Shipping $shipping Shipping method.
	 */
	public function __construct( Settings $settings, Shipping $shipping ) {
		$this->settings = $settings;
		$this->shipping = $shipping;
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'admin_notices', [ $this, 'notices' ] );
	}

	/**
	 * Show notices
	 */
	public function notices() {
		$this->empty_api_key();
		$this->shipping_method_enable();
	}

	/**
	 * Notice empty api key
	 */
	private function empty_api_key() {
		if ( ! empty( $this->settings->api_key() ) ) {
			return;
		}
		$this->show(
			'error',
			sprintf(
			/* translators: 1: link on page option */
				__(
					'For the plugin to work, you must enter the API key on the <a href="%s">plugin settings page</a>',
					'woo-nova-poshta'
				),
				get_admin_url( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
			)
		);
	}

	/**
	 * Shipping method not enabled
	 */
	private function shipping_method_enable() {
		if ( $this->shipping->is_active() ) {
			return;
		}
		$this->show(
			'error',
			sprintf(
			/* translators: 1: link on WooCommerce settings */
				__(
					'You must add the "New Delivery Method" delivery method <a href="%s">in the WooCommerce settings</a>',
					'woo-nova-poshta',
				),
				get_admin_url( null, 'admin.php?page=wc-settings&tab=shipping' )
			)
		);
	}

	/**
	 * Show notice
	 *
	 * @param string $type    Type of notice.
	 * @param string $message Message of notice.
	 */
	private function show( string $type, string $message ) {
		require plugin_dir_path( __FILE__ ) . 'partials/notice.php';
	}

}
