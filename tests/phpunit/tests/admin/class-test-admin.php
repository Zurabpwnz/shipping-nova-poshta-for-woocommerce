<?php
/**
 * Admin area tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Admin;

use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Class Test_Admin
 *
 * @package Nova_Poshta\Admin
 */
class Test_Admin extends TestCase {

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	/**
	 * End test
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test construct
	 */
	public function test___construct() {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		new Admin( $api, $settings );
	}

}
