<?php
/**
 * Product Category Metabox
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Main;

/**
 * Class Product_Category_Metabox
 *
 * @package Nova_Poshta\Admin
 */
class Product_Category_Metabox {

	/**
	 * Nonce
	 */
	const NONCE = Main::PLUGIN_SLUG . '-product-cat-formulas';
	/**
	 * Nonce field name
	 */
	const NONCE_FIELD = Main::PLUGIN_SLUG . '_nonce';

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'product_cat_add_form_fields', [ $this, 'add' ] );
		add_action( 'product_cat_edit_form_fields', [ $this, 'edit' ] );
		add_action( 'edited_product_cat', [ $this, 'save' ] );
		add_action( 'create_product_cat', [ $this, 'save' ] );
	}

	/**
	 * Add metabox html on product_cat add page
	 */
	public function add() {
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-add.php';
	}

	/**
	 * Add metabox html on product_cat edit page
	 *
	 * @param object $term current term.
	 */
	public function edit( $term ) {
		$weight_formula = get_term_meta( $term->term_id, 'weight_formula', true );
		$width_formula  = get_term_meta( $term->term_id, 'width_formula', true );
		$length_formula = get_term_meta( $term->term_id, 'length_formula', true );
		$height_formula = get_term_meta( $term->term_id, 'height_formula', true );

		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-edit.php';
	}

	/**
	 * Save product_cat fields to Database
	 *
	 * @param int $term_id current term.
	 */
	public function save( int $term_id ) {
		$nonce = filter_input( INPUT_POST, self::NONCE_FIELD, FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			return;
		}
		$weight_formula = filter_input( INPUT_POST, 'weight_formula', FILTER_SANITIZE_STRING );
		$width_formula  = filter_input( INPUT_POST, 'width_formula', FILTER_SANITIZE_STRING );
		$length_formula = filter_input( INPUT_POST, 'length_formula', FILTER_SANITIZE_STRING );
		$height_formula = filter_input( INPUT_POST, 'height_formula', FILTER_SANITIZE_STRING );

		update_term_meta( $term_id, 'weight_formula', (string) $weight_formula );
		update_term_meta( $term_id, 'width_formula', (string) $width_formula );
		update_term_meta( $term_id, 'length_formula', (string) $length_formula );
		update_term_meta( $term_id, 'height_formula', (string) $height_formula );
	}

}
