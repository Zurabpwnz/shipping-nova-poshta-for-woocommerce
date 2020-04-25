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

/**
 * Class Admin
 *
 * @package Nova_Poshta\Admin
 */
class Notice {

	/**
	 * List of notices
	 *
	 * @var array
	 */
	private $notices = [];

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

}
