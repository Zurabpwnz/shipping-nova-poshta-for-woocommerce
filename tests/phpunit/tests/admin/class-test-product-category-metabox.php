<?php
/**
 * Product category metabox tests
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 */

namespace Nova_Poshta\Admin;

use Mockery;
use Nova_Poshta\Tests\Test_Case;
use tad\FunctionMocker\FunctionMocker;
use WP_Mock;

/**
 * Class Test_Product_Category_Metabox
 *
 * @package Nova_Poshta\Admin
 */
class Test_Product_Category_Metabox extends Test_Case {

	/**
	 * Test adding hooks
	 */
	public function test_hooks() {
		$product_category_metabox = new Product_Category_Metabox();
		WP_Mock::expectActionAdded( 'product_cat_add_form_fields', [ $product_category_metabox, 'add' ] );
		WP_Mock::expectActionAdded( 'product_cat_edit_form_fields', [ $product_category_metabox, 'edit' ] );
		WP_Mock::expectActionAdded( 'edited_product_cat', [ $product_category_metabox, 'save' ] );
		WP_Mock::expectActionAdded( 'create_product_cat', [ $product_category_metabox, 'save' ] );

		$product_category_metabox->hooks();
	}

	/**
	 * Test add metabox
	 */
	public function test_add() {
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once();
		WP_Mock::userFunction( 'wp_nonce_field' )->
		with( Product_Category_Metabox::NONCE, Product_Category_Metabox::NONCE_FIELD, false )->
		once();
		$product_category_metabox = new Product_Category_Metabox();
		ob_start();

		$product_category_metabox->add();
		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test edit metabox
	 */
	public function test_edit() {
		$weight        = 10;
		$width         = 20;
		$length        = 30;
		$height        = 40;
		$term          = Mockery::mock( 'WP_Term' );
		$term->term_id = 100;
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $term->term_id, 'weight_formula', true )->
		once()->
		andReturn( $weight );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $term->term_id, 'width_formula', true )->
		once()->
		andReturn( $width );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $term->term_id, 'length_formula', true )->
		once()->
		andReturn( $length );
		WP_Mock::userFunction( 'get_term_meta' )->
		with( $term->term_id, 'height_formula', true )->
		once()->
		andReturn( $height );
		WP_Mock::userFunction( 'plugin_dir_path' )->
		once();
		WP_Mock::userFunction( 'wp_nonce_field' )->
		with( Product_Category_Metabox::NONCE, Product_Category_Metabox::NONCE_FIELD, false )->
		once();
		$product_category_metabox = new Product_Category_Metabox();
		ob_start();

		$product_category_metabox->edit( $term );
		$this->assertNotEmpty( ob_get_clean() );
	}

	/**
	 * Test don't save metabox with invalid nonce
	 */
	public function test_NOT_save_with_invalid_nonce() {
		$term_id = 10;
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		with( null, Product_Category_Metabox::NONCE )->
		once()->
		andReturn( false );
		$product_category_metabox = new Product_Category_Metabox();

		$product_category_metabox->save( $term_id );
	}

	/**
	 * Test save metabox
	 */
	public function test_save() {
		$term_id = 10;
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
		WP_Mock::userFunction( 'wp_verify_nonce' )->
		with( $nonce, Product_Category_Metabox::NONCE )->
		once()->
		andReturn( true );
		WP_Mock::userFunction( 'update_term_meta' )->
		with( $term_id, 'weight_formula', (string) $weight )->
		once();
		WP_Mock::userFunction( 'update_term_meta' )->
		with( $term_id, 'width_formula', (string) $width )->
		once();
		WP_Mock::userFunction( 'update_term_meta' )->
		with( $term_id, 'height_formula', (string) $height )->
		once();
		WP_Mock::userFunction( 'update_term_meta' )->
		with( $term_id, 'length_formula', (string) $length )->
		once();
		$product_category_metabox = new Product_Category_Metabox();

		$product_category_metabox->save( $term_id );
	}

}
