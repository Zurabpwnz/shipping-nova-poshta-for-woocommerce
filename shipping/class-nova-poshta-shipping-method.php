<?php
if ( ! class_exists( 'WC_Your_Shipping_Method' ) ) {
	class Nova_Poshta_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor for your shipping class
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'woo_nova_poshta';
			$this->title              = __( 'Доставка Новой почтой', 'woo-nova-poshta' );
			$this->method_title       = __( 'Доставка Новой почтой', 'woo-nova-poshta' );
			$this->method_description = __( 'Доставка Новой почтой', 'woo-nova-poshta' );
			$this->enabled            = true;
			$this->supports           = [
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			];
			$this->init();
			parent::__construct( $instance_id );
		}

		/**
		 * Init your settings
		 */
		public function init() {
			$this->init_form_fields();
			$this->init_settings();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function init_form_fields() {
			$this->instance_form_fields = [
				'title' => [
					'title'   => __( 'Заголовок метода', 'woo-nova-poshta' ),
					'type'    => 'text',
					'default' => __( 'Доставка Новой почты', 'woo-nova-poshta' ),
				],
			];
		}

		/**
		 * Calculate_shipping function.
		 */
		public function calculate_shipping( $package = [] ) {
			$rate = [
				'id'      => $this->id,
				'label'   => $this->title,
				'cost'    => '0',
				'package' => $package,
			];

			$this->add_rate( $rate );
		}

	}
}

