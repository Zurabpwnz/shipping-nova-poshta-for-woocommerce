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
		$current_language       = apply_filters( 'shipping_nova_poshta_for_woocommerce_current_language', get_locale() );
		$this->current_language = 'uk' === $current_language ? 'ua' : 'ru';
	}

	/**
	 * Get current language
	 */
	public function get_current_language() {
		return $this->current_language;
	}

}
