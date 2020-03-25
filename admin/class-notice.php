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
				'Для работы плагина неоходимо ввести API ключ на <a href="%s">странице настроек плагина</a>',
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
				'Необходимо добавить метод доставки "Доставка Новой почты"
				<a href="%s">в настройках WooCommerce</a>',
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
		require_once plugin_dir_path( __FILE__ ) . 'partials/notice.php';
	}

}
