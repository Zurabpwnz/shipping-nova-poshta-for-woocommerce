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
	 * Add hooks
	 */
	public function hooks() {
		add_filter( 'shipping_nova_poshta_for_woocommerce_default_city', [ $this, 'default_city' ] );
	}

	/**
	 * Get current language
	 */
	public function get_current_language() {
		if ( $this->current_language ) {
			return $this->current_language;
		}
		$current_language       = apply_filters( 'shipping_nova_poshta_for_woocommerce_current_language', get_locale() );
		$this->current_language = in_array( $current_language, [ 'uk_UA', 'uk' ], true ) ? 'ua' : 'ru';

		return $this->current_language;
	}

	/**
	 * Default city
	 *
	 * @return string
	 */
	public function default_city() {
		return 'ua' === $this->get_current_language() ? 'Київ' : 'Киев';
	}

}
