<?php

namespace Krya;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

class Test_Krya extends Test_Case {

	public function setUp(): void {
		FunctionMocker::setUp();
		WP_Mock::setUp();
		parent::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
		FunctionMocker::tearDown();
	}

	public function test_1() {
		\WP_Mock::userFunction( 'get_current_user_id' )->once();
		get_current_user_id();
	}

	public function test_2() {
		get_current_user_id();
	}

}
