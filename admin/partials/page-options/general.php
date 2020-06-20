<?php
/**
 * General settings
 *
 * @package Nova_Posta\Admin\Partials
 */

use Nova_Poshta\Core\Main;

?>
<h1><?php esc_attr_e( 'General', 'shipping-nova-poshta-for-woocommerce' ); ?></h1>
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
					'If you do not have an API key, then you can get it in the <a href="%s" target="_blank">personal account of Nova Poshta</a>. Unfortunately, without the API key, the plugin will not work :(',
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
					<?php esc_attr_e( 'Sender Phone', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
					<input
							type="tel"
							name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[phone]"
							placeholder="+380991234567"
							required="required"
							value="<?php echo esc_attr( $this->settings->phone() ); ?>"/>
				</label>
			</p>
			<p>
				<label>
					<?php esc_attr_e( 'Description of your products', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
					<span class="with-help-tip">
						<input
								type="tel"
								name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[description]"
								value="<?php echo esc_attr( $this->settings->description() ); ?>"
								required="required"/>
						<span
								class="help-tip"
								data-tip="<?php esc_attr_e( 'A few words about what you send. For example: toys, shoes, household appliances, etc.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
						></span>
					</span>
				</label>
			</p>
			<p>
				<?php
				$current_city_id = $this->settings->city_id();
				$current_city    = $current_city_id ? $this->api->city( $current_city_id ) : '';
				if ( ! $current_city ) {
					$cities          = $this->api->cities( '', 0 );
					$city            = $this->api->cities(
						apply_filters(
							'shipping_nova_poshta_for_woocommerce_default_city',
							'',
							get_current_user_id(),
							$this->language->get_current_language()
						),
						1
					);
					$current_city_id = array_keys( $city )[0];
					$current_city    = array_pop( $city );
				}
				?>
				<label>
					<?php esc_attr_e( 'Sender City', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
					<select
							id="shipping_nova_poshta_for_woocommerce_city"
							name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[city_id]"
							required="required">
						<option
								value="<?php echo esc_attr( $current_city_id ); ?>"><?php echo esc_attr( $current_city ); ?></option>
					</select>
				</label>
			</p>
			<p>
				<?php
				$warehouses           = $current_city_id ? $this->api->warehouses( $current_city_id ) : [];
				$current_warehouse_id = $this->settings->warehouse_id();
				?>
				<label>
					<?php esc_attr_e( 'Sender Warehouse', 'shipping-nova-poshta-for-woocommerce' ); ?><br>
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
			<div class="cost-formula-fields">
				<h2><?php esc_attr_e( 'Cost calculate formulas', 'shipping-nova-poshta-for-woocommerce' ); ?></h2>
				<p>
					<label>
						<input
								type="checkbox"
								name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[is_shipping_cost_enable]"
								value="1" <?php checked( $this->settings->is_shipping_cost_enable(), true ); ?>
						/>
						<?php esc_attr_e( 'Enable shipping cost', 'shipping-nova-poshta-for-woocommerce' ); ?>
					</label>
				</p>
				<?php if ( $this->settings->is_shipping_cost_enable() ) { ?>
					<p>
						<label>
							<?php esc_attr_e( 'Default weight formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
							<br>
							<span class="with-help-tip">
								<input
										type="text"
										name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[default_weight_formula]"
										value="<?php echo esc_attr( $this->settings->default_weight_formula() ); ?>"
										required="required"/>
								<span
										class="help-tip"
										data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in kilograms. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
								></span>
							</span>
						</label>
					</p>
					<p>
						<label>
							<?php esc_attr_e( 'Default width formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
							<br>
							<span class="with-help-tip">
								<input
										type="text"
										name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[default_width_formula]"
										value="<?php echo esc_attr( $this->settings->default_width_formula() ); ?>"
										required="required"/>
								<span
										class="help-tip"
										data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
								></span>
							</span>
						</label>
					</p>
					<p>
						<label>
							<?php esc_attr_e( 'Default lenght formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
							<br>
							<span class="with-help-tip">
								<input
										type="text"
										name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[default_length_formula]"
										value="<?php echo esc_attr( $this->settings->default_length_formula() ); ?>"
										required="required"/>
								<span
										class="help-tip"
										data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
								></span>
							</span>
						</label>
					</p>
					<p>
						<label>
							<?php esc_attr_e( 'Default height formula', 'shipping-nova-poshta-for-woocommerce' ); ?>
							<br>
							<span class="with-help-tip">
								<input
										type="text"
										name="<?php echo esc_attr( Main::PLUGIN_SLUG ); ?>[default_height_formula]"
										value="<?php echo esc_attr( $this->settings->default_height_formula() ); ?>"
										required="required"/>
								<span
										class="help-tip"
										data-tip="<?php esc_attr_e( 'Formula cost calculation. The numbers are indicated in meters. You can use the [qty] shortcode to indicate the number of products.', 'shipping-nova-poshta-for-woocommerce' ); ?>"
								></span>
							</span>
						</label>
					</p>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<?php submit_button(); ?>
</form>
