<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h2><?php esc_attr_e( 'Create invoice', 'woo-nova-poshta' ); ?></h2>
<form action="" method="POST" class="woo-nova-poshta-form">
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s last name:', 'woo-nova-poshta' ); ?><br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[last_name]"
					value=""/>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s first name:', 'woo-nova-poshta' ); ?><br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[first_name]"
					value=""/>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Recipient\'s phone:', 'woo-nova-poshta' ); ?><br>
			<input
					type="tel"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[phone]"
					value=""/>
		</label>
	</p>
	<p>
		<?php

		$city            = $this->api->cities(
			apply_filters( 'woo_nova_poshta_default_city', 'Киев' ),
			1
		);
		$current_city_id = array_keys( $city )[0] ?? '';
		$current_city    = array_values( $city )[0] ?? '';
		?>
		<label>
			<?php esc_attr_e( 'Recipient\'s city:', 'woo-nova-poshta' ); ?><br>
			<select
					id="woo_nova_poshta_city"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[city]">
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
			<?php esc_attr_e( 'Recipient\'s warehouse:', 'woo-nova-poshta' ); ?><br>
			<select
					id="woo_nova_poshta_warehouse"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[warehouse]">
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
			<?php esc_attr_e( 'Assessed value:', 'woo-nova-poshta' ); ?><br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[price]">
		</label>
	</p>
	<p>
		<label>
			<input
					type="checkbox"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[backward]">
			<?php esc_attr_e( 'Return shipping', 'woo-nova-poshta' ); ?>
		</label>
	</p>
	<p>
		<label>
			<?php esc_attr_e( 'Remittance', 'woo-nova-poshta' ); ?><br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[redelivery]">
		</label>
	</p>
</form>
