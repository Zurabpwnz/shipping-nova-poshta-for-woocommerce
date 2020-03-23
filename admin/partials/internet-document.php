<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h2>Создание накладной</h2>
<form action="" method="POST" class="woo-nova-poshta-form">
	<p>
		<label>
			Фамилия получателя:<br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[last_name]"
					value=""/>
		</label>
	</p>
	<p>
		<label>
			Имя получателя:<br>
			<input
					type="text"
					name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[first_name]"
					value=""/>
		</label>
	</p>
	<p>
		<label>
			Номер телефона<br>
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
		$current_warehouse_id = array_keys( $warehouses )[0] ?? '';
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
	<p>
		<label>
			Оценочная стоимость<br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[price]">
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[backward]">Обрантная доставка
		</label>
	</p>
	<p>
		<label>
			Денежный перевод<br>
			<input type="number" name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[redelivery]">
		</label>
	</p>
</form>
