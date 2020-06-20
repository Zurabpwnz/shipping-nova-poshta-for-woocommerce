<?php
/**
 * Settings
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Admin\Notice\Notice;

/**
 * Class Settings
 *
 * @package Nova_Poshta\Core
 */
class Settings {

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	private $options;
	/**
	 * Plugin notices.
	 *
	 * @var Notice
	 */
	private $notice;

	/**
	 * Settings constructor.
	 *
	 * @param Notice $notice Plugin notices.
	 */
	public function __construct( Notice $notice ) {
		$this->options = get_option( Main::PLUGIN_SLUG, [] );
		$this->notice  = $notice;
		$this->notices();
	}

	/**
	 * Register notices.
	 */
	private function notices() {
		if ( ! $this->api_key() ) {
			$this->notice->add(
				'error',
				sprintf(
				/* translators: 1: link on page option */
					__(
						'For the plugin to work, you must enter the API key on the <a href="%s">plugin settings page</a>',
						'shipping-nova-poshta-for-woocommerce'
					),
					get_admin_url( null, 'admin.php?page=' . Main::PLUGIN_SLUG )
				)
			);
		}
	}

	/**
	 * API key
	 *
	 * @return string
	 */
	public function api_key(): string {
		return $this->options['api_key'] ?? '';
	}

	/**
	 * Admin phone
	 *
	 * @return string
	 */
	public function phone(): string {
		return $this->options['phone'] ?? '';
	}

	/**
	 * Package description
	 *
	 * @return string
	 */
	public function description(): string {
		return $this->options['description'] ?? 'Товар';
	}

	/**
	 * Admin city_id
	 *
	 * @return string
	 */
	public function city_id(): string {
		return $this->options['city_id'] ?? '';
	}

	/**
	 * Admin warehouse id
	 *
	 * @return string
	 */
	public function warehouse_id(): string {
		return $this->options['warehouse_id'] ?? '';
	}

	/**
	 * Is a shipping cost enable
	 *
	 * @return bool
	 */
	public function is_shipping_cost_enable(): bool {
		return ! empty( $this->options['is_shipping_cost_enable'] );
	}

	/**
	 * Default formula for calculate weight of products in order
	 *
	 * @return string
	 */
	public function default_weight_formula(): string {
		return $this->options['default_weight_formula'] ?? '[qty] * 0.5';
	}

	/**
	 * Default formula for calculate width of products in order
	 *
	 * @return string
	 */
	public function default_width_formula(): string {
		return $this->options['default_width_formula'] ?? '[qty] * 0.26';
	}

	/**
	 * Default formula for calculate length of products in order
	 *
	 * @return string
	 */
	public function default_length_formula(): string {
		return $this->options['default_length_formula'] ?? '[qty] * 0.145';
	}

	/**
	 * Default formula for calculate height of products in order
	 *
	 * @return string
	 */
	public function default_height_formula(): string {
		return $this->options['default_height_formula'] ?? '[qty] * 0.1';
	}

}
