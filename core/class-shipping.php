<?php
/**
 * Shipping
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Core;

use Nova_Poshta\Admin\Notice\Notice;
use Nova_Poshta\Core\Cache\Factory_Cache;

/**
 * Class Shipping
 *
 * @package Nova_Poshta\Core
 */
class Shipping {

	/**
	 * Shipping method name
	 *
	 * @var string
	 */
	const METHOD_NAME = 'shipping_nova_poshta_for_woocommerce';
	/**
	 * Plugin notices
	 *
	 * @var Notice
	 */
	private $notice;
	/**
	 * Cache
	 *
	 * @var Factory_Cache
	 */
	private $factory_cache;

	/**
	 * Shipping constructor.
	 *
	 * @param Notice        $notice        Plugin notices.
	 * @param Factory_Cache $factory_cache Cache.
	 */
	public function __construct( Notice $notice, Factory_Cache $factory_cache ) {
		$this->notice        = $notice;
		$this->factory_cache = $factory_cache;
		$this->notices();
	}

	/**
	 * Register notices.
	 */
	private function notices() {
		if ( ! $this->is_active() ) {
			$this->notice->add(
				'error',
				sprintf(
				/* translators: 1: link on WooCommerce settings */
					__(
						'You must add the "Nova Poshta" shipping method <a href="%s">in the WooCommerce settings</a>',
						'shipping-nova-poshta-for-woocommerce'
					),
					get_admin_url( null, 'admin.php?page=wc-settings&tab=shipping' )
				)
			);
		}
	}

	/**
	 * Require shipping methods
	 *
	 * @codeCoverageIgnore
	 */
	public function require_methods() {
		require_once plugin_dir_path( __DIR__ ) . 'shipping/class-nova-poshta-shipping-method.php';
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'woocommerce_shipping_init', [ $this, 'require_methods' ] );

		add_filter( 'woocommerce_shipping_methods', [ $this, 'register_methods' ] );
	}

	/**
	 * Register shipping method
	 *
	 * @param array $methods Shipping methods.
	 *
	 * @return array
	 */
	public function register_methods( array $methods ): array {
		$methods[ self::METHOD_NAME ] = 'Nova_Poshta_Shipping_Method';

		return $methods;
	}

	/**
	 * Is shipping method active
	 *
	 * @return bool
	 */
	private function is_active(): bool {
		global $wpdb;
		$cache = $this->factory_cache->object();
		//phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$is_active = $cache->get( self::METHOD_NAME . '_active' );
		if ( ! $is_active ) {
			//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$is_active = (bool) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT `instance_id` FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods
			WHERE `method_id` = %s AND `is_enabled` = 1 LIMIT 1',
					self::METHOD_NAME
				)
			);
			$cache->set( self::METHOD_NAME . '_active', $is_active, constant( 'DAY_IN_SECONDS' ) );
		}

		//phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching

		return $is_active;
	}

}
