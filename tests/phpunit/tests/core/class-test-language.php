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
	 * Test adding hooks
	 */
	public function test_hooks() {
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

}
