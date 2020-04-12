<?php
/**
 * Main tests
 *
 * @package   Woo-Nova-Poshta
 */

namespace Nova_Poshta\Core;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use WP_Mock;

/**
 * Class Test_Main
 *
 * @package Nova_Poshta\Core
 */
class Test_Main extends Test_Case {

	/**
	 * Test init all hooks
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_init() {
		// TODO: Actions and Filters does't work.
		// todo: Because of hard dependencies.
		// todo: Or, send all dependencies to the constructor from the main file.
		// todo: Or, move all new() statements to the constructor, store in protected fields, and fill up protected fields with set_protected_property().
		// todo: so far, it is not a test at all.
		Mockery::mock( 'overload:Nova_Poshta\Core\Settings' );
		Mockery::mock( 'overload:Nova_Poshta\Core\DB' );
		Mockery::mock( 'overload:Nova_Poshta\Core\API' );
		Mockery::mock( 'overload:Nova_Poshta\Admin\Admin' );
		Mockery::mock( 'overload:Nova_Poshta\Admin\Notice' );
		Mockery::mock( 'overload:Nova_Poshta\Admin\User' );
		Mockery::mock( 'overload:Nova_Poshta\Front\Front' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Shipping' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Notice' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Checkout' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Order' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Thank_You' );
		Mockery::mock( 'overload:Nova_Poshta\Core\Language' );

		WP_Mock::userFunction( 'plugin_dir_path' )->
		once();
		WP_Mock::userFunction( 'plugin_basename' )->
		once()->
		andReturn( 'path/to/main-file' );
		WP_Mock::userFunction( 'register_activation_hook' )->
		once();

		$main = new Main();

		$main->init();
	}

}
