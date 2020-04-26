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
	public function test_get_current_language( $locale, $current_lang ) {
		WP_Mock::userFunction( 'get_locale' )->
		once()->
		andReturn( $locale );
		$language       = new Language();
		$resul_language = $language->get_current_language();

		$this->assertSame( $current_lang, $resul_language );
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
			[ 'en', 'ru' ],
		];
	}

}
