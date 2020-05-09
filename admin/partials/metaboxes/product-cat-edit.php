<?php
/**
 * Product category edit metabox html
 *
 * @package Nova_Posta\Admin\Partials
 * @var string $weight_formula
 * @var string $width_formula
 * @var string $length_formula
 * @var string $height_formula
 */

use Nova_Poshta\Admin\Product_Category_Metabox;

wp_nonce_field( Product_Category_Metabox::NONCE, Product_Category_Metabox::NONCE_FIELD, false );
?>
<tr class="form-field shipping-nova-poshta-for-woocommerce-form">
	<th scope="row" valign="top">
		<label
				for="weight_formula"><?php esc_attr_e( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
	</th>
	<td>
		<span class="with-help-tip">
			<input
					type="text"
					id="weight_formula"
					value="<?php echo esc_attr( $weight_formula ); ?>"
					name="weight_formula"/>
			<span
					class="help-tip"
					data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
			></span>
		</span>
	</td>
</tr>
<tr class="form-field shipping-nova-poshta-for-woocommerce-form">
	<th scope="row" valign="top">
		<label
				for="width_formula"><?php esc_attr_e( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
	</th>
	<td>
		<span class="with-help-tip">
			<input
					type="text"
					id="width_formula"
					value="<?php echo esc_attr( $width_formula ); ?>"
					name="width_formula"/>
			<span
					class="help-tip"
					data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
			></span>
		</span>
	</td>
</tr>
<tr class="form-field shipping-nova-poshta-for-woocommerce-form">
	<th scope="row" valign="top">
		<label
				for="length_formula"><?php esc_attr_e( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
	</th>
	<td>
		<span class="with-help-tip">
			<input
					type="text"
					id="length_formula"
					value="<?php echo esc_attr( $length_formula ); ?>"
					name="length_formula"/>
			<span
					class="help-tip"
					data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
			></span>
		</span>
	</td>
</tr>
<tr class="form-field shipping-nova-poshta-for-woocommerce-form">
	<th scope="row" valign="top">
		<label
				for="height_formula"><?php esc_attr_e( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
	</th>
	<td>
		<span class="with-help-tip">
			<input
					type="text"
					id="height_formula"
					value="<?php echo esc_attr( $height_formula ); ?>"
					name="height_formula"/>
			<span
					class="help-tip"
					data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
			></span>
		</span>
	</td>
</tr>
