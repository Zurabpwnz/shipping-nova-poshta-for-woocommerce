<?php

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Main;

/**
 * Class Product_Metabox
 *
 * @package Nova_Poshta\Admin
 */
class Product_Metabox {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_product_options_shipping', [ $this, 'add_metabox_html' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_metabox' ] );
	}

	/**
	 * Add metabox html on product page (Shipment tab)
	 */
	public function add_metabox_html() {
		global $post;
		$product = wc_get_product( $post->ID );
		require plugin_dir_path( __FILE__ ) . 'partials/metaboxes/product.php';
	}

	/**
	 * Save metabox field in Database
	 *
	 * @param integer $post_id current product.
	 */
	public function save_metabox( $post_id ) {

		$nonce = filter_input( INPUT_POST, Main::PLUGIN_SLUG . '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-product-formulas' ) ) {
			return;
		}

		$weight_formula = filter_input( INPUT_POST, 'weight_formula', FILTER_SANITIZE_STRING );
		$width_formula  = filter_input( INPUT_POST, 'width_formula', FILTER_SANITIZE_STRING );
		$length_formula = filter_input( INPUT_POST, 'length_formula', FILTER_SANITIZE_STRING );
		$height_formula = filter_input( INPUT_POST, 'height_formula', FILTER_SANITIZE_STRING );

		$product = wc_get_product( $post_id );

		$product->update_meta_data( 'weight_formula', (string) $weight_formula );
		$product->update_meta_data( 'width_formula', (string) $width_formula );
		$product->update_meta_data( 'length_formula', (string) $length_formula );
		$product->update_meta_data( 'height_formula', (string) $height_formula );

		$product->save();
	}

}
