<?php
/**
 * Front area
 *
 * @package   Woo-Nova-Poshta
 * @author    Maksym Denysenko
 * @link      https://github.com/mdenisenko/woo-nova-poshta
 * @copyright Copyright (c) 2020
 * @license   GPL-2.0+
 * @wordpress-plugin
 */

namespace Nova_Poshta\Front;

use Nova_Poshta\Core\Main;

/**
 * Class Admin
 *
 * @package Nova_Poshta\Front
 */
class Front {

	/**
	 * Add hooks
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue styles
	 */
	public function enqueue_styles() {
		if ( ! is_checkout() ) {
			return;
		}
		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'assets/css/select2.min.css', [], Main::VERSION, 'all' );
		wp_enqueue_style( Main::PLUGIN_SLUG, plugin_dir_url( __FILE__ ) . 'assets/css/main.css', [ 'select2' ], Main::VERSION, 'all' );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( ! is_checkout() ) {
			return;
		}
		wp_enqueue_script(
			'select2',
			plugin_dir_url( __FILE__ ) . 'assets/js/select2.min.js',
			[ 'jquery' ],
			Main::VERSION,
			true
		);
		wp_enqueue_script(
			Main::PLUGIN_SLUG,
			plugin_dir_url( __FILE__ ) . 'assets/js/main.js',
			[
				'jquery',
				'select2',
			],
			Main::VERSION,
			true
		);
		wp_localize_script(
			Main::PLUGIN_SLUG,
			'woo_nova_poshta',
			[
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( Main::PLUGIN_SLUG ),
			]
		);
	}

}
