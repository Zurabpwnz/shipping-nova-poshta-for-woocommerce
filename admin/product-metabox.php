<?php

namespace Nova_Poshta\Admin;

class Product_Metabox {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_product_options_shipping', [ $this, 'add_metabox_html' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_metabox' ] );
	}

	/**
	 * Super comment
	 */
	public function add_metabox_html() {
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product.php';
	}

	/**
	 * Super comment
	 *
	 * @param integer $post_id current term.
	 */
	public function save_metabox( $post_id ) {

		$weight_formula = filter_input( INPUT_POST, 'weight_formula', FILTER_SANITIZE_STRING );
		$width_formula  = filter_input( INPUT_POST, 'width_formula', FILTER_SANITIZE_STRING );
		$length_formula = filter_input( INPUT_POST, 'length_formula', FILTER_SANITIZE_STRING );
		$height_formula = filter_input( INPUT_POST, 'height_formula', FILTER_SANITIZE_STRING );

		update_post_meta( $post_id, 'weight_formula', (string) $weight_formula );
		update_post_meta( $post_id, 'width_formula', (string) $width_formula );
		update_post_meta( $post_id, 'length_formula', (string) $length_formula );
		update_post_meta( $post_id, 'height_formula', (string) $height_formula );
	}

}