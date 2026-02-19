<?php
/**
 * Health History Admin Page
 *
 * Registers and manages the Health History admin page.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.602.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Analytics\Health_History;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health History Page Class
 *
 * @since 1.602.0200
 */
class Health_History_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_menu'            => 'register_menu',
			'admin_enqueue_scripts' => 'enqueue_assets',
		);
	}

	/**
	 * Initialize the page (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Health_History_Page::subscribe() instead
	 * @since      1.602.0200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Register admin menu page.
	 *
	 * @since 1.602.0200
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
	 * @since 1.602.0200
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
		}

		require_once WPSHADOW_PATH . 'includes/ui/templates/health-history.php';
	}

	/**
	 * Enqueue assets for the page.
	 *
	 * @since  1.602.0200
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

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-health-history-charts',
			'wpShadowHealthHistory',
			'wpshadow_get_health_history'
		);
	}
}
