<?php
/**
 * Admin area notices
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

/**
 * Class Notice
 *
 * @package Nova_Poshta\Admin\Notice
 */
class Notice extends Abstract_Notice {

	/**
	 * Cache key
	 */
	const NOTICES_KEY = 'np-notices';
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
		$notices               = $this->transient_cache->get( self::NOTICES_KEY );
		if ( is_array( $notices ) ) {
			$this->transient_cache->delete( self::NOTICES_KEY );
			$this->notices = $notices;
		}
	}

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'admin_notices', [ $this, 'notices' ] );
		add_action( 'shutdown', [ $this, 'save' ] );
	}

	/**
	 * Show notices
	 */
	public function notices() {
		if ( empty( $this->notices ) ) {
			return;
		}
		foreach ( $this->notices as $notice ) {
			$this->show( $notice['type'], $notice['message'], $notice['btn_label'], $notice['btn_url'] );
		}
		$this->notices = [];
	}

	/**
	 * Save notices on one minute
	 */
	public function save() {
		if ( ! empty( $this->notices ) ) {
			$this->transient_cache->set( self::NOTICES_KEY, $this->notices, 60 );
		}
	}

}
