<?php
/**
 * Abstract notice
 *
 * @package   Shipping-Nova-Poshta-For-Woocommerce
 * @author    Maksym Denysenko
 * @link      https://github.com/wppunk/shipping-nova-poshta-for-woocommerce
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Admin\Notice;

/**
 * Class Abstract_Notice
 *
 * @package Nova_Poshta\Notice\Admin
 */
abstract class Abstract_Notice {

	/**
	 * List of notices
	 *
	 * @var array
	 */
	protected $notices = [];

	/**
	 * Register plugin notice
	 *
	 * @param string $type      Type of notice.
	 * @param string $message   Message of notice.
	 * @param string $btn_label Button label.
	 * @param string $btn_url   Button url.
	 */
	public function add( string $type, string $message, string $btn_label = '', string $btn_url = '' ) {
		$this->notices[] = [
			'type'      => $type,
			'message'   => $message,
			'btn_label' => $btn_label,
			'btn_url'   => $btn_url,
		];
	}

	/**
	 * Show notices
	 */
	abstract public function notices();

	/**
	 * Show notice
	 *
	 * @param string $type      Type of notice.
	 * @param string $message   Message of notice.
	 * @param string $btn_label Button label.
	 * @param string $btn_url   Button url.
	 */
	protected function show( string $type, string $message, string $btn_label = '', string $btn_url = '' ) {
		require plugin_dir_path( __DIR__ ) . 'partials/notice.php';
	}

}
