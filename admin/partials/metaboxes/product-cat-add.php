<?php
/**
 * Product category add metabox html
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Admin\Product_Category_Metabox;

?>
<div class="cost-formula-fields shipping-nova-poshta-for-woocommerce-form">
	<h2><?php esc_attr_e( 'Cost calculate formulas', 'shipping-nova-poshta-for-woocommerce' ); ?></h2>
	<?php wp_nonce_field( Product_Category_Metabox::NONCE, Product_Category_Metabox::NONCE_FIELD, false ); ?>
	<div class="form-field">
		<label><?php esc_attr_e( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<p class="with-help-tip">
				<input
						type="text"
						name="weight_formula"/>
				<span
						class="help-tip"
						data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
				></span>
			</p>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<p class="with-help-tip">
				<input
						type="text"
						name="width_formula"/>
				<span
						class="help-tip"
						data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
				></span>
			</p>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<p class="with-help-tip">
				<input
						type="text"
						name="length_formula"/>
				<span
						class="help-tip"
						data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
				></span>
			</p>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<p class="with-help-tip">
				<input
						type="text"
						name="height_formula"/>
				<span
						class="help-tip"
						data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
				></span>
			</p>
		</label>
	</div>
</div>
