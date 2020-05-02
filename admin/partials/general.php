<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h2><?php esc_attr_e( 'General', 'shipping-nova-poshta-for-woocommerce' ); ?></h2>
<form action="options.php" method="POST" class="shipping-nova-poshta-for-woocommerce-form">
	<?php settings_errors( Main::PLUGIN_SLUG ); ?>
	<?php settings_fields( Main::PLUGIN_SLUG ); ?>
	<p>
		<label><?php esc_attr_e( 'API key', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[api_key]"
					value="<?php echo esc_attr( $this->settings->api_key() ); ?>"/>
		</label>
		<?php
		echo wp_kses_post(
			sprintf(
			/* translators: 1: Link on Nova Poshta personal account */
				__(
					'If you do not have an API key, then you can get it in the <a href="%s" target="_blank">personal account of Nova Poshta</a>',
					'shipping-nova-poshta-for-woocommerce'
				),
				'https://new.novaposhta.ua/#/1/settings/developers'
			)
		);
		?>
	</p>
	<?php if ( $this->settings->api_key() ) { ?>
	<div>
		<p>
			<label>
				<?php esc_attr_e( 'Phone', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
				<input
						type="tel"
						name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[phone]"
						value="<?php echo esc_attr( $this->settings->phone() ); ?>"/>
			</label>
		</p>
		<p>
			<label>
				<?php esc_attr_e( 'Description of your products', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
				<input
						type="tel"
						name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[description]"
						value="<?php echo esc_attr( $this->settings->description() ); ?>"/>
			</label>
		</p>
		<p>
			<?php
			$current_city_id = $this->settings->city_id();
			$current_city    = $current_city_id ? $this->api->city( $current_city_id ) : '';
			?>
			<label>
				<?php esc_attr_e( 'City', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
				<select
						id="shipping_nova_poshta_for_woocommerce_city"
						name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[city_id]">
					<option value="<?php echo esc_attr( $current_city_id ); ?>"><?php echo esc_attr( $current_city ); ?></option>
				</select>
			</label>
		</p>
		<p>
			<?php
			$warehouses           = $current_city_id ? $this->api->warehouses( $current_city_id ) : [];
			$current_warehouse_id = $this->settings->warehouse_id();
			?>
			<label>
				<?php esc_attr_e( 'Warehouse', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
				<select
						id="shipping_nova_poshta_for_woocommerce_warehouse"
						name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[warehouse_id]">
					<?php foreach ( $warehouses as $warehouse_id => $name ) { ?>
						<option
							<?php selected( $warehouse_id, $current_warehouse_id, true ); ?>
								value="<?php echo esc_attr( $warehouse_id ); ?>"
						><?php echo esc_attr( $name ); ?></option>
					<?php } ?>
				</select>
			</label>
		</p>
	</div>
	<?php } ?>
	<?php submit_button(); ?>
</form>
