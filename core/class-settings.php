<?php
/**
 * Settings
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

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
	 * Settings constructor.
	 */
	public function __construct() {
		$this->options = get_option( Main::PLUGIN_SLUG, [] );
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

}
