<?php
/**
 * Languages
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

/**
 * Class Language
 *
 * @package Nova_Poshta\Core
 */
class Language {

	/**
	 * Current site language
	 *
	 * @var string
	 */
	private $current_language;

	/**
	 * Language constructor.
	 */
	public function __construct() {
		$this->current_language = 'uk' === get_locale() ? 'ua' : 'ru';
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'load' ] );
	}

	/**
	 * Load translate
	 */
	public function load() {
		load_plugin_textdomain(
			Main::PLUGIN_SLUG,
			false,
			dirname( plugin_basename( __DIR__ ) ) . '/languages/'
		);
	}

	/**
	 * Get current language
	 */
	public function get_current_language() {
		return $this->current_language;
	}

}
