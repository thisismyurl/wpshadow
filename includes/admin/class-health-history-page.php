<?php
/**
 * Health History Admin Page
 *
 * Registers and manages the Health History admin page.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.2602.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Analytics\Health_History;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health History Page Class
 *
 * @since 1.2602.0200
 */
class Health_History_Page {

	/**
	 * Initialize the page.
	 *
	 * @since 1.2602.0200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menu page.
	 *
	 * @since 1.2602.0200
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'wpshadow',
			__( 'Health History', 'wpshadow' ),
			__( 'Health History', 'wpshadow' ),
			'manage_options',
			'wpshadow-health-history',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the page.
	 *
	 * @since 1.2602.0200
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
		}

		require_once WPSHADOW_PATH . 'includes/views/health-history.php';
	}

	/**
	 * Enqueue assets for the page.
	 *
	 * @since  1.2602.0200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-health-history' !== $hook ) {
			return;
		}

		// Enqueue Chart.js from CDN (loaded dynamically in JS).
		wp_enqueue_script(
			'wpshadow-health-history-charts',
			WPSHADOW_URL . 'assets/js/health-history-charts.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-health-history-charts',
			'wpShadowHealthHistory',
			array(
				'nonce'  => wp_create_nonce( 'wpshadow_get_health_history' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
}
