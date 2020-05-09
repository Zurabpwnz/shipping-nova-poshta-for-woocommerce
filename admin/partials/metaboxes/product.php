<?php
/**
 * Product metabox html
 *
 * @package Nova_Posta\Admin\Partials
 * @var WC_Product $product
 */

use Nova_Poshta\Admin\Product_Metabox;

echo '</div><div class="options_group">';

wp_nonce_field( Product_Metabox::NONCE, Product_Metabox::NONCE_FIELD, false );

woocommerce_wp_text_input(
	[
		'id'          => 'weight_formula',
		'label'       => __( 'Formula for weight calculate', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'weight_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'width_formula',
		'label'       => __( 'Formula for width calculate', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'width_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'length_formula',
		'label'       => __( 'Formula for length calculate', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'length_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'height_formula',
		'label'       => __( 'Formula for height calculate', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'height_formula', true ),
	]
);

