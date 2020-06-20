<?php
/**
 * Nova Poshta Shipping Method
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

use Nova_Poshta\Admin\Notice\Notice;
use Nova_Poshta\Core\API;
use Nova_Poshta\Core\Cache\Factory_Cache;
use Nova_Poshta\Core\Cache\Object_Cache;
use Nova_Poshta\Core\Cache\Transient_Cache;
use Nova_Poshta\Core\Calculator;
use Nova_Poshta\Core\DB;
use Nova_Poshta\Core\Language;
use Nova_Poshta\Core\Main;
use Nova_Poshta\Core\Settings;
use Nova_Poshta\Core\Shipping_Cost;

if ( ! class_exists( 'Nova_Poshta_Shipping_Method' ) ) {
	/**
	 * Class Nova_Poshta_Shipping_Method
	 */
	class Nova_Poshta_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Unique ID for the shipping method - must be set.
		 *
		 * @var string
		 */
		public $id;
		/**
		 * Shipping method title for the frontend.
		 *
		 * @var string
		 */
		public $title;
		/**
		 * Method title.
		 *
		 * @var string
		 */
		public $method_title;
		/**
		 * Method description.
		 *
		 * @var string
		 */
		public $method_description;
		/**
		 * Features this method supports. Possible features used by core:
		 * - shipping-zones Shipping zone functionality + instances
		 * - instance-settings Instance settings screens.
		 * - settings Non-instance settings screens. Enabled by default for BW compatibility with methods before instances existed.
		 * - instance-settings-modal Allows the instance settings to be loaded within a modal in the zones UI.
		 *
		 * @var array
		 */
		public $supports;

		/**
		 * Constructor for your shipping class
		 *
		 * @param int $instance_id Instance ID.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'shipping_nova_poshta_for_woocommerce';
			$this->title              = __( 'Nova Poshta delivery', 'shipping-nova-poshta-for-woocommerce' );
			$this->method_title       = __( 'Nova Poshta delivery', 'shipping-nova-poshta-for-woocommerce' );
			$this->method_description = __( 'Nova Poshta delivery', 'shipping-nova-poshta-for-woocommerce' );
			$this->enabled            = 'yes';
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
					'title'   => __( 'Method header', 'shipping-nova-poshta-for-woocommerce' ),
					'type'    => 'text',
					'default' => __( 'Nova Poshta delivery', 'shipping-nova-poshta-for-woocommerce' ),
				],
			];
		}

		/**
		 * Calculate shipping method.
		 *
		 * Important method!
		 *
		 * @access public
		 *
		 * @param array $package Packages.
		 *
		 * @return void
		 * @throws Exception Invalid DateTime.
		 */
		public function calculate_shipping( $package = [] ) {
			$transient_cache = new Transient_Cache();
			$object_cache    = new Object_Cache();
			$factory_cache   = new Factory_Cache( $transient_cache, $object_cache );
			$notice          = new Notice( $transient_cache );
			$language        = new Language();
			$db              = new DB( $language );
			$settings        = new Settings( $notice );
			$api             = new API( $db, $factory_cache, $settings );
			$user_id         = get_current_user_id();
			$city_id         = apply_filters( 'shipping_nova_poshta_for_woocommerce_default_city_id', '', $user_id );
			if ( ! $city_id ) {
				$city    = $api->cities(
					apply_filters(
						'shipping_nova_poshta_for_woocommerce_default_city',
						'',
						$user_id,
						$language->get_current_language()
					),
					1
				);
				$city_id = array_keys( $city )[0];
			}
			$nonce = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_nonce', FILTER_SANITIZE_STRING );
			if ( wp_verify_nonce( $nonce, Main::PLUGIN_SLUG . '-shipping' ) ) {
				$request_city_id = filter_input( INPUT_POST, 'shipping_nova_poshta_for_woocommerce_city', FILTER_SANITIZE_STRING );
				$city_id         = ! empty( $request_city_id ) ? $request_city_id : $city_id;
			}
			$cost = 0;
			global $woocommerce;
			$products = $woocommerce->cart->get_cart_contents();
			if ( $city_id && ! empty( $products ) ) {
				$calculator    = new Calculator();
				$shipping_cost = new Shipping_Cost( $api, $settings, $calculator );
				$cost          = $shipping_cost->calculate( $city_id, $products );
			}
			$rate = [
				'id'       => $this->id,
				'label'    => $this->title,
				'calc_tax' => 'per_item',
				'cost'     => $cost,
			];

			// Register the rate.
			$this->add_rate( $rate );
		}

		/**
		 * Processes and saves global shipping method options in the admin area.
		 *
		 * This method is usually attached to woocommerce_update_options_x hooks.
		 *
		 * @return bool
		 * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
		 */
		public function process_admin_options() {
			return parent::process_admin_options();
		}
		//phpcs:enable Generic.CodeAnalysis.UselessOverridingMethod.Found

	}
}
