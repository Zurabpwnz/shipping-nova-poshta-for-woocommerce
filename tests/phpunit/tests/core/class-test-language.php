<?php
/**
 * Ajax tests
 *
 * @package   Woo-Nova-Poshta
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
	 * Test adding hooks
	 */
	public function test_hooks() {
		WP_Mock::userFunction( 'get_locale' )->once();
		$language = new Language();

		WP_Mock::expectActionAdded( 'plugins_loaded', [ $language, 'load' ] );

		$language->hooks();
	}

	/**
	 * Test search cities
	 */
	public function test_load() {
		WP_Mock::userFunction( 'plugin_basename' )->
		once();
		WP_Mock::userFunction( 'load_plugin_textdomain' )->
		withArgs( [ Main::PLUGIN_SLUG, false, '/languages/' ] )->
		once();
		$language = new Language();

		$language->load();
	}

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
