<?php
/**
 * Nova Poshta COD Gateway
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

use Nova_Poshta\Core\Shipping;
use Nova_Poshta\Core\Calculator;

if ( ! class_exists( 'WC_Gateway_COD' ) ) {
	return;
}

/**
 * Class Nova_Poshta_Gateway_COD
 */
class Nova_Poshta_Gateway_COD extends WC_Gateway_COD {

	/**
	 * Gateway ID.
	 */
	const ID = 'nova_poshta_cod_gateway';
	/**
	 * Prepayment string. Maybe formula or number.
	 *
	 * @var string
	 */
	private $prepayment;
	/**
	 * Instructions on thank you page and email.
	 *
	 * @var string
	 */
	public $instructions;
	/**
	 * Description of method.
	 *
	 * @var string
	 */
	public $description;
	/**
	 * Calculator
	 *
	 * @var Calculator
	 */
	private $calculator;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		parent::__construct();
		$this->calculator         = new Calculator();
		$this->prepayment         = $this->settings['prepayment'] ?? '';
		$this->enable_for_methods = [ Shipping::METHOD_NAME ];
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->plugin_id          = 'shipping_nova_poshta_for_woocommerce';
		$this->id                 = self::ID;
		$this->method_title       = __( 'Nova Poshta cash on delivery', 'shipping-nova-poshta-for-woocommerce' );
		$this->method_description = __( 'Have your customers pay with cash (or by other means) upon delivery.', 'shipping-nova-poshta-for-woocommerce' );
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'      => [
				'title'       => __( 'Enable/Disable', 'shipping-nova-poshta-for-woocommerce' ),
				'label'       => __( 'Enable cash on delivery', 'shipping-nova-poshta-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			],
			'title'        => [
				'title'       => __( 'Title', 'shipping-nova-poshta-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'shipping-nova-poshta-for-woocommerce' ),
				'default'     => __( 'Cash on delivery', 'shipping-nova-poshta-for-woocommerce' ),
				'desc_tip'    => true,
			],
			'prepayment'   => [
				'title'       => __( 'Prepayment', 'shipping-nova-poshta-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Formula cost calculation. The numbers are indicated in current currency. You can use the [qty] shortcode to indicate the number of products. Leave a empty if you work without prepayment.', 'shipping-nova-poshta-for-woocommerce' ),
				'default'     => '100 + ( [qty] - 1 ) * 20',
				'desc_tip'    => true,
			],
			'description'  => [
				'title'       => __( 'Description', 'shipping-nova-poshta-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Description of the payment method that the customer will see on the checkout page. You can use the shortcode [prepayment] to write the price of prepayment and [rest] shortcode to write the rest of the amount.', 'shipping-nova-poshta-for-woocommerce' ),
				'default'     => __( 'Pay [prepayment] prepayment and [rest] with cash upon delivery.', 'shipping-nova-poshta-for-woocommerce' ),
				'desc_tip'    => true,
			],
			'instructions' => [
				'title'       => __( 'Instructions', 'shipping-nova-poshta-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page. You can use the shortcode [prepayment] to write the price of prepayment and [rest] shortcode to write the rest of the amount.', 'shipping-nova-poshta-for-woocommerce' ),
				'default'     => __( 'Pay [prepayment] prepayment and [rest] with cash upon delivery.', 'shipping-nova-poshta-for-woocommerce' ),
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( ! $this->instructions ) {
			return;
		}
		echo wp_kses_post( wpautop( wptexturize( $this->prepare_text( $this->instructions ) ) ) );
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order         Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text    Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->prepare_text( $this->instructions ) ) ) . PHP_EOL );
		}
	}

	/**
	 * Payment description on checkout page.
	 */
	public function payment_fields() {
		if ( ! $this->description ) {
			return;
		}
		echo wp_kses_post( wpautop( wptexturize( '<p>' . $this->prepare_text( $this->description ) . '</p>' ) ) );
	}

	/**
	 * Replace text shortcodes in text.
	 *
	 * @param string $text Something text.
	 *
	 * @return string
	 */
	private function prepare_text( string $text ): string {
		if ( ! $this->prepayment ) {
			return $text;
		}
		if ( ! WC()->cart ) {
			return $text;
		}
		$quantity   = array_sum( WC()->cart->get_cart_item_quantities() );
		$total      = WC()->cart->get_total( 'no-view' );
		$prepayment = $this->calculator->result( $this->prepayment, $quantity );
		$rest       = $total - $prepayment;
		if ( $rest <= 0 ) {
			return $text;
		}

		$text = preg_replace( '/\[prepayment]/', wc_price( $prepayment ), $text );
		$text = preg_replace( '/\[rest]/', wc_price( $rest ), $text );

		return $text;
	}

}
