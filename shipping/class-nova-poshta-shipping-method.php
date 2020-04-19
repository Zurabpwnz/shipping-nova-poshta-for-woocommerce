<?php
/**
 * Nova Poshta Shipping Method
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

if ( ! class_exists( 'WC_Your_Shipping_Method' ) ) {
	/**
	 * Class Nova_Poshta_Shipping_Method
	 */
	class Nova_Poshta_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor for your shipping class
		 *
		 * @param int $instance_id Instance ID.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'woo_nova_poshta';
			$this->title              = __( 'Nova Poshta delivery', 'woo-nova-poshta' );
			$this->method_title       = __( 'Nova Poshta delivery', 'woo-nova-poshta' );
			$this->method_description = __( 'Nova Poshta delivery', 'woo-nova-poshta' );
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

		/**
		 * Init form fields
		 */
		public function init_form_fields() {
			$this->instance_form_fields = [
				'title' => [
					'title'   => __( 'Method header', 'woo-nova-poshta' ),
					'type'    => 'text',
					'default' => __( 'Nova Poshta delivery', 'woo-nova-poshta' ),
				],
			];
		}

	}
}
