<?php
/**
 * Languages
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
 * Class Language
 *
 * @package Nova_Poshta\Core
 */
class Language {

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

}
