<?php
/**
 * Product metabox html
 *
 * @package Nova_Posta\Admin\Partials
 */

global $post;
echo '</div><div class="options_group">';

woocommerce_wp_text_input(
	[
		'id'          => 'weight_formula',
		'label'       => __( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Enter the custom value here.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => get_post_meta( $post->ID, 'weight_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'width_formula',
		'label'       => __( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Enter the custom value here.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => get_post_meta( $post->ID, 'width_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'length_formula',
		'label'       => __( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Enter the custom value here.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => get_post_meta( $post->ID, 'length_formula', true ),
	]
);

woocommerce_wp_text_input(
	[
		'id'          => 'height_formula',
		'label'       => __( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ),
		'placeholder' => '',
		'desc_tip'    => 'true',
		'description' => __( 'Enter the custom value here.', 'shipping-nova-poshta-for-woocommerce' ),
		'value'       => get_post_meta( $post->ID, 'height_formula', true ),
	]
);
