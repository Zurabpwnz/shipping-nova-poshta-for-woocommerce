<?php
/**
 * Product metabox html
 *
 * @package Nova_Posta\Admin\Partials
 * @var WC_Product $product
 */

use Nova_Poshta\Core\Main;

echo '</div><div class="options_group">';

woocommerce_wp_text_input(
	[
		'id'          => 'weight_formula',
		'label'       => __( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula for weight calculate.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'weight_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'width_formula',
		'label'       => __( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula for width calculate.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'width_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'length_formula',
		'label'       => __( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula for length calculate.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'length_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'height_formula',
		'label'       => __( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Formula for height calculate.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => $product->get_meta( 'height_formula', true ),
	]
);

wp_nonce_field( Main::PLUGIN_SLUG . '-product-formulas', Main::PLUGIN_SLUG . '_nonce', false );
