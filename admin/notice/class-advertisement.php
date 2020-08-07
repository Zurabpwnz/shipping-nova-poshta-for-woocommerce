<?php
/**
 * Advertisement messages for customers.
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin\Notice;

use Nova_Poshta\Core\Cache\Transient_Cache;
use Nova_Poshta\Core\Main;

/**
 * Class Advertisement
 *
 * @package Nova_Poshta\Admin
 */
class Advertisement extends Abstract_Notice {

	/**
	 * Transient cache
	 *
	 * @var Transient_Cache
	 */
	private $transient_cache;

	/**
	 * Notice constructor.
	 *
	 * @param Transient_Cache $transient_cache Transient Cache.
	 */
	public function __construct( Transient_Cache $transient_cache ) {
		$this->transient_cache = $transient_cache;
	}

	/**
	 * Init hooks.
	 */
	public function hooks() {
		add_action( 'admin_notices', [ $this, 'notices' ] );
		add_action( 'wp_ajax_shipping_nova_poshta_for_woocommerce_notice', [ $this, 'close' ] );
	}

	/**
	 * Show advertisement
	 */
	public function notices() {
		global $current_screen;
		if ( 0 !== strpos( $current_screen->base, 'toplevel_page_' . Main::PLUGIN_SLUG ) ) {
			return;
		}
		if ( $this->transient_cache->get( 'advertisement' ) ) {
			return;
		}
		$advertisement = $this->get_advertisement();
		$this->show(
			'info',
			$advertisement['message'],
			$advertisement['btn_label'],
			$advertisement['btn_url']
		);
	}

	/**
	 * Get random advertisement
	 *
	 * @return array
	 */
	private function get_advertisement() {
		$get_advertisement = [
			[
				'message'   => sprintf(
				/* translators: %s - stars icons */
					__( 'Hey, do you like our plugin? Please, could you rate it and set %s stars for us. We very care about your emotion and comfortability.', 'shipping-nova-poshta-for-woocommerce' ),
					'<span class="stars"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></span>'
				),
				'btn_label' => __( 'Rate plugin', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_url'   => 'https://wordpress.org/support/plugin/shipping-nova-poshta-for-woocommerce/reviews/#new-post',
			],
			[
				'message'   => __( 'If you found a bug or have an idea for a new feature tell us. Let\'s go make the plugin better.', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_label' => __( 'Report a bug', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_url'   => 'https://wordpress.org/support/plugin/shipping-nova-poshta-for-woocommerce/#new-topic-0',
			],
			[
				'message'   => __( 'Plugin free but developers want a eat too. You can support our project and donate to spaghetti.', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_label' => __( 'Donate', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_url'   => 'https://www.liqpay.ua/ru/checkout/checkout_1592508401976666_24299491_PLWbH7gXrHGvRKVZYii3',
			],
			[
				'message'   => __( 'If you a developer you can give respect to us just pick on the star on GitHub.', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_label' => __( 'Show respect', 'shipping-nova-poshta-for-woocommerce' ),
				'btn_url'   => 'https://github.com/wppunk/shipping-nova-poshta-for-woocommerce',
			],
		];

		return $get_advertisement[ wp_rand( 0, count( $get_advertisement ) - 1 ) ];
	}

	/**
	 * Close on some time.
	 */
	public function close() {
		check_ajax_referer( Main::PLUGIN_SLUG, 'nonce' );
		$this->transient_cache->set( 'advertisement', 1, 7 * constant( 'DAY_IN_SECONDS' ) );
		wp_send_json( true );
	}

}
