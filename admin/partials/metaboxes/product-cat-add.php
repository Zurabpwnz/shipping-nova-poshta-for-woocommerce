<?php
/**
 * Product category add metabox html
 *
 * @package Nova_Posta\Admin\Partials
 */

?>
<div class="cost-formula-fields">
	<h2><?php esc_attr_e( 'Cost calculate formulas', 'shipping-nova-poshta-for-woocommerce' ); ?></h2>
	<div class="form-field">
		<label><?php esc_attr_e( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<input
				type="text"
				name="weight_formula"/>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<input
				type="text"
				name="width_formula"/>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<input
				type="text"
				name="length_formula"/>
		</label>
	</div>
	<div class="form-field">
		<label><?php esc_attr_e( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
			<input
				type="text"
				name="height_formula"/>
		</label>
	</div>
</div>
