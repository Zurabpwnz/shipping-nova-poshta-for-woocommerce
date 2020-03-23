<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h2>Основые настройки</h2>
<form action="options.php" method="POST" class="woo-nova-poshta-form">
	<?php settings_fields( Main::PLUGIN_SLUG ); ?>
	<p>
		<label>API ключ<br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[api_key]"
					value="<?php echo isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key'] ) : ''; ?>"/>
		</label>
		Если у вас нет API ключа, то вы можете получить его на
		<a href="my.novaposhta.ua/settings/index#apikeys" target="_blank">my.novaposhta.ua/settings/index#apikeys</a>
	</p>
	<p>
		<label>
			Номер телефона<br>
			<input
					type="tel"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[phone]"
					value="<?php echo isset( $this->options['phone'] ) ? esc_attr( $this->options['phone'] ) : ''; ?>"/>
		</label>
	</p>
	<p>
		<?php
		$current_city_id = $this->options['city'] ?? '';
		$current_city    = $current_city_id ? $this->api->city( $current_city_id ) : '';
		?>
		<label>
			Город<br>
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
		$current_warehouse_id = $this->options['warehouse'] ?? '';
		?>
		<label>
			Отделение<br>
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
	<?php submit_button(); ?>
</form>
