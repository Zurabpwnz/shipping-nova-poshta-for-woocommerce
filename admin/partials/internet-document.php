<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h2><?php esc_attr_e( 'Create invoice', 'shipping-nova-poshta-for-woocommerce' ); ?></h2>
<form action="" method="POST" class="shipping-nova-poshta-for-woocommerce-form">
	<?php wp_nonce_field( Main::PLUGIN_SLUG . '-invoice', Main::PLUGIN_SLUG . '_nonce', false ); ?>
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s last name:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[last_name]"
					value="" required="required"/>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s first name:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[first_name]"
					value="" required="required"/>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s phone:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input
					type="tel"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[phone]"
					value="" required="required"/>
		</label>
	</p>
	<p>
		<?php

		$city            = $this->api->cities(
			apply_filters( 'shipping_nova_poshta_for_woocommerce_default_city', 'Киев' ),
			1
		);
		$current_city_id = array_keys( $city )[0] ?? '';
		$current_city    = array_values( $city )[0] ?? '';
		?>
		<label>
			<?php esc_attr_e( 'Recipient\'s city:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<select
					id="shipping_nova_poshta_for_woocommerce_city"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[city_id]"
					required="required">
				<option value="<?php echo esc_attr( $current_city_id ); ?>"><?php echo esc_attr( $current_city ); ?></option>
			</select>
		</label>
	</p>
	<p>
		<?php
		$warehouses           = $current_city_id ? $this->api->warehouses( $current_city_id ) : [];
		$current_warehouse_id = array_keys( $warehouses )[0] ?? '';
		?>
		<label>
			<?php esc_attr_e( 'Recipient\'s warehouse:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<select
					id="shipping_nova_poshta_for_woocommerce_warehouse"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[warehouse_id]"
					required="required">
				<?php foreach ( $warehouses as $warehouse_id => $name ) { ?>
					<option
						<?php selected( $warehouse_id, $current_warehouse_id, true ); ?>
							value="<?php echo esc_attr( $warehouse_id ); ?>"
					><?php echo esc_attr( $name ); ?></option>
				<?php } ?>
			</select>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Assessed value:', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[price]" required="required">
		</label>
	</p>
	<p>
		<label>
			<input
					type="checkbox"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[backward]">
			<?php esc_attr_e( 'Return shipping', 'shipping-nova-poshta-for-woocommerce' ); ?>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Remittance', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[redelivery]">
		</label>
	</p>
	<?php submit_button(); ?>
</form>
