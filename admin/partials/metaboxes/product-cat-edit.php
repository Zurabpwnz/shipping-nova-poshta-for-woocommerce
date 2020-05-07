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

use Nova_Poshta\Core\Main;

?>
<div class="cost-formula-fields">
	<tr class="form-field">
		<th scope="row" valign="top">
			<label
				for="weight_formula"><?php esc_attr_e( 'Weight formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
		</th>
		<td><input
				type="text"
				id="weight_formula"
				value="<?php echo esc_attr( $weight_formula ); ?>"
				name="weight_formula"/>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label
				for="width_formula"><?php esc_attr_e( 'Width formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
		</th>
		<td><input
				type="text"
				id="width_formula"
				value="<?php echo esc_attr( $width_formula ); ?>"
				name="width_formula"/>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label
				for="length_formula"><?php esc_attr_e( 'Length formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
		</th>
		<td><input
				type="text"
				id="length_formula"
				value="<?php echo esc_attr( $length_formula ); ?>"
				name="length_formula"/>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top">
			<label
				for="height_formula"><?php esc_attr_e( 'Height formula', 'shipping-nova-poshta-for-woocommerce' ); ?></label>
		</th>
		<td><input
				type="text"
				id="height_formula"
				value="<?php echo esc_attr( $height_formula ); ?>"
				name="height_formula"/>
		</td>
	</tr>
	<?php wp_nonce_field( Main::PLUGIN_SLUG . '-product-cat-formulas', Main::PLUGIN_SLUG . '_nonce', false ); ?>
</div>
