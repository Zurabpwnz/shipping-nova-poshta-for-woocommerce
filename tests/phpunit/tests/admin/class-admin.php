<?php

class Test_Admin extends \PHPUnit\Framework\TestCase {

	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	public function test___construct() {
		$api      = Mockery::mock( 'Nova_Poshta\Core\API' );
		$settings = Mockery::mock( 'Nova_Poshta\Core\Settings' );
		new \Nova_Poshta\Admin\Admin( $api, $settings );
	}

}
