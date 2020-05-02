<?php
/**
 * Ajax tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Language
 *
 * @package Nova_Poshta\Core
 */
class Test_Language extends Test_Case {

	/**
	 * Test search cities
	 *
	 * @dataProvider dp_locale
	 *
	 * @param string $locale       site locale.
	 * @param string $current_lang expected language.
	 */
	public function test_get_current_language( string $locale, string $current_lang ) {
		WP_Mock::userFunction( 'get_locale' )->
		once()->
		andReturn( $locale );
		$language = new Language();

		$this->assertSame( $current_lang, $language->get_current_language() );
		$this->assertSame( $current_lang, $language->get_current_language() );
	}

	/**
	 * Return variant of WordPress locales
	 * Data provider for test_get_current_language().
	 *
	 * @return array
	 */
	public function dp_locale() {
		return [
			[ 'ru_RU', 'ru' ],
			[ 'uk', 'ua' ],
			[ 'uk_UA', 'ua' ],
			[ 'en', 'ru' ],
		];
	}

	/**
	 * Test hooks added
	 */
	public function test_hooks() {
		$language = new Language();
		WP_Mock::expectFilterAdded(
			'shipping_nova_poshta_for_woocommerce_default_city',
			[
				$language,
				'default_city',
			]
		);

		$language->hooks();
	}

	/**
	 * Test default city
	 *
	 * @dataProvider dp_default_city
	 *
	 * @param string $locale       site locale.
	 * @param string $default_city expected default city.
	 */
	public function test_default_city( string $locale, string $default_city ) {
		WP_Mock::userFunction( 'get_locale' )->
		once()->
		andReturn( $locale );
		$language = new Language();

		$this->assertSame( $default_city, $language->default_city() );
	}

	/**
	 * Return variant of WordPress locales
	 * Data provider for test_get_current_language().
	 *
	 * @return array
	 */
	public function dp_default_city() {
		return [
			[ 'ru_RU', 'Киев' ],
			[ 'uk', 'Київ' ],
			[ 'uk_UA', 'Київ' ],
			[ 'en', 'Киев' ],
		];
	}

}
