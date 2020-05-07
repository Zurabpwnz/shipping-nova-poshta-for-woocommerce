<?php

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Main;

/**
 * Class Product_Category_Metabox
 *
 * @package Nova_Poshta\Admin
 */
class Product_Category_Metabox {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'product_cat_add_form_fields', [ $this, 'add_metabox_html' ] );
		add_action( 'product_cat_edit_form_fields', [ $this, 'edit_metabox_html' ] );

		add_action( 'edited_product_cat', [ $this, 'save_metabox' ] );
		add_action( 'create_product_cat', [ $this, 'save_metabox' ] );
	}

	/**
	 * Add metabox html on product_cat add page
	 */
	public function add_metabox_html() {
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-add.php';
	}

	/**
	 * Add metabox html on product_cat edit page
	 *
	 * @param object $term current term.
	 */
	public function edit_metabox_html( $term ) {
		$weight_formula = get_term_meta( $term->term_id, 'weight_formula', true );
		$width_formula  = get_term_meta( $term->term_id, 'width_formula', true );
		$length_formula = get_term_meta( $term->term_id, 'length_formula', true );
		$height_formula = get_term_meta( $term->term_id, 'height_formula', true );

		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-edit.php';
	}

	/**
	 * Save product_cat fields to Database
	 *
	 * @param integer $term_id current term.
	 */
	public function save_metabox( $term_id ) {
		$nonce = filter_input( INPUT_POST, Main::PLUGIN_SLUG . '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-product-cat-formulas' ) ) {
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
