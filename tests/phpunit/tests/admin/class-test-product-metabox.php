<?php
/**
 * Product metabox tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Brain\Monkey\Expectation\Exception\ExpectationArgsRequired;
use Mockery;
use Nova_Poshta\Tests\Test_Case;
use stdClass;
use tad\FunctionMocker\FunctionMocker;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

/**
 * Class Test_Product_Metabox
 *
 * @package Nova_Poshta\Admin
 */
class Test_Product_Metabox extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$product_metabox = new Product_Metabox();

		$product_metabox->hooks();

		$this->assertTrue( has_action( 'woocommerce_product_options_shipping', [ $product_metabox, 'add' ] ) );
		$this->assertTrue( has_action( 'woocommerce_process_product_meta', [ $product_metabox, 'save' ] ) );
	}

	/**
	 * Test add metabox
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_add() {
		global $post;
		//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post     = new stdClass();
		$post->ID = 15;
		$weight   = 10;
		$width    = 20;
		$length   = 30;
		$height   = 40;
		$product  = Mockery::mock( 'WC_Product' );
		$product
			->shouldReceive( 'get_meta' )
			->with( 'weight_formula', true )
			->once()
			->andReturn( $weight );
		$product
			->shouldReceive( 'get_meta' )
			->with( 'width_formula', true )
			->once()
			->andReturn( $width );
		$product
			->shouldReceive( 'get_meta' )
			->with( 'length_formula', true )
			->once()
			->andReturn( $length );
		$product
			->shouldReceive( 'get_meta' )
			->with( 'height_formula', true )
			->once()
			->andReturn( $height );
		when( '__' )->returnArg();
		expect( 'plugin_dir_path' )
			->withAnyArgs()
			->once();
		expect( 'wc_get_product' )
			->with( $post->ID )
			->once()
			->andReturn( $product );
		expect( 'wp_nonce_field' )
			->with( Product_Metabox::NONCE, Product_Metabox::NONCE_FIELD, false )
			->once();
		expect( 'woocommerce_wp_text_input' )
			->with(
				[
					'id'          => 'weight_formula',
					'label'       => 'Formula for weight calculate',
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.',
					'value'       => $weight,
				]
			)
			->once();
		expect( 'woocommerce_wp_text_input' )
			->with(
				[
					'id'          => 'width_formula',
					'label'       => 'Formula for width calculate',
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.',
					'value'       => $width,
				]
			)
			->once();
		expect( 'woocommerce_wp_text_input' )
			->with(
				[
					'id'          => 'length_formula',
					'label'       => 'Formula for length calculate',
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.',
					'value'       => $length,
				]
			)
			->once();
		expect( 'woocommerce_wp_text_input' )
			->with(
				[
					'id'          => 'height_formula',
					'label'       => 'Formula for height calculate',
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.',
					'value'       => $height,
				]
			)
			->once();
		$product_metabox = new Product_Metabox();
		ob_start();

		$product_metabox->add();
		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test don't save metabox with invalid nonce
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_NOT_save_with_invalid_nonce() {
		$post_id = 10;
		expect( 'wp_verify_nonce' )
			->with( null, Product_Metabox::NONCE )
			->once()
			->andReturn( false );
		$product_metabox = new Product_Metabox();

		$product_metabox->save( $post_id );
	}

	/**
	 * Test save metabox
	 *
	 * @throws ExpectationArgsRequired Invalid arguments.
	 */
	public function test_save() {
		$post_id = 10;
		$nonce   = 'nonce';
		$weight  = 10;
		$width   = 20;
		$length  = 30;
		$height  = 40;
		FunctionMocker::replace(
			'filter_input',
			function () use ( $nonce, $weight, $width, $length, $height ) {
				static $i = 0;

				$answers = [ $nonce, $weight, $width, $length, $height ];

				return $answers[ $i ++ ];
			}
		);
		expect( 'wp_verify_nonce' )
			->with( $nonce, Product_Metabox::NONCE )
			->once()
			->andReturn( true );
		$product = Mockery::mock( 'WC_Product' );
		$product
			->shouldReceive( 'update_meta_data' )
			->with( 'weight_formula', (string) $weight )
			->once();
		$product
			->shouldReceive( 'update_meta_data' )
			->with( 'width_formula', (string) $width )
			->once();
		$product
			->shouldReceive( 'update_meta_data' )
			->with( 'length_formula', (string) $length )
			->once();
		$product
			->shouldReceive( 'update_meta_data' )
			->with( 'height_formula', (string) $height )
			->once();
		$product
			->shouldReceive( 'save' )
			->once();
		expect( 'wc_get_product' )
			->with( $post_id )
			->once()
			->andReturn( $product );
		$product_metabox = new Product_Metabox();

		$product_metabox->save( $post_id );
	}

}
