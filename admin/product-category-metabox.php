<?php

namespace Nova_Poshta\Admin;

class Product_Category_Metabox {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'product_cat_add_form_fields', [ $this, 'add_metabox_html' ], 10, 1 );
		add_action( 'product_cat_edit_form_fields', [ $this, 'edit_metabox_html' ], 10, 1 );

		add_action( 'edited_product_cat', [ $this, 'save_metabox' ], 10, 1 );
		add_action( 'create_product_cat', [ $this, 'save_metabox' ], 10, 1 );
	}

	/**
	 * Super comment
	 */
	public function add_metabox_html() {
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-add.php';
	}

	/**
	 * Super comment
	 *
	 * @param object $term current term.
	 */
	public function edit_metabox_html( $term ) {
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product-cat-edit.php';
	}

	/**
	 * Super comment
	 *
	 * @param integer $term_id current term.
	 */
	public function save_metabox( $term_id ) {

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