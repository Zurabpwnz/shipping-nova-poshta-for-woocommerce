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

namespace Nova_Poshta\Admin;

use Nova_Poshta\Core\Cache\Transient_Cache;

/**
 * Class Admin
 *
 * @package Nova_Poshta\Admin
 */
class Notice {

	/**
	 * Cache key
	 */
	const NOTICES_KEY = 'np-notices';
	/**
	 * List of notices
	 *
	 * @var array
	 */
	private $notices = [];
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
	 * Register plugin notice
	 *
	 * @param string $type    Type of notice.
	 * @param string $message Message of notice.
	 */
	public function add( string $type, string $message ) {
		$this->notices[] = [
			'type'    => $type,
			'message' => $message,
		];
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
			$this->show( $notice['type'], $notice['message'] );
		}
		$this->notices = [];
	}

	/**
	 * Show notice
	 *
	 * @param string $type    Type of notice.
	 * @param string $message Message of notice.
	 */
	private function show( string $type, string $message ) {
		require plugin_dir_path( __FILE__ ) . 'partials/notice.php';
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
