<?php
/**
 * Phase 4 Infrastructure Initializer
 *
 * Registers REST API endpoints and enqueues assets for Phase 4 features.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phase4_Initializer Class
 *
 * Initializes Phase 4 infrastructure components.
 *
 * @since 0.6093.1200
 */
class Phase4_Initializer {

	/**
	 * Initialize Phase 4 features
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init() {
		// Register REST API endpoints
		add_action( 'rest_api_init', array( __CLASS__, 'register_api_endpoints' ) );

		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		// Initialize cron schedules
		add_action( 'init', array( __CLASS__, 'register_cron_schedules' ) );

		// Register AJAX handlers (already registered in their files)
		self::register_ajax_handlers();
	}

	/**
	 * Register REST API endpoints
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_api_endpoints() {
		Report_Integration_Manager::register_api_endpoints();
	}

	/**
	 * Enqueue Phase 4 assets
	 *
	 * @since 0.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only load on WPShadow pages
		if ( strpos( $hook, 'wpshadow' ) === false ) {
			return;
		}

		// Enqueue JavaScript
		wp_enqueue_script(
			'wpshadow-phase4',
			WPSHADOW_URL . 'assets/js/wpshadow-phase4.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue CSS
		wp_enqueue_style(
			'wpshadow-phase4',
			WPSHADOW_URL . 'assets/css/wpshadow-phase4.css',
			array(),
			WPSHADOW_VERSION
		);

		// Localize script with nonces and data
		wp_localize_script(
			'wpshadow-phase4',
			'wpShadowPhase4',
			array(
				'nonces' => array(
					'export_report'       => wp_create_nonce( 'wpshadow_export_report' ),
					'save_snapshot'       => wp_create_nonce( 'wpshadow_save_snapshot' ),
					'compare_snapshots'   => wp_create_nonce( 'wpshadow_compare_snapshots' ),
					'get_trend_data'      => wp_create_nonce( 'wpshadow_get_trend_data' ),
					'add_annotation'      => wp_create_nonce( 'wpshadow_add_annotation' ),
					'send_integration'    => wp_create_nonce( 'wpshadow_send_integration' ),
					'calculate_analytics' => wp_create_nonce( 'wpshadow_calculate_analytics' ),
				),
				'userId'  => get_current_user_id(),
				'siteUrl' => home_url(),
			)
		);
	}

	/**
	 * Register cron schedules
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register_cron_schedules() {
		// Cleanup old exports
		if ( ! wp_next_scheduled( 'wpshadow_cleanup_exports' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_cleanup_exports' );
		}

		// Cleanup old snapshots
		if ( ! wp_next_scheduled( 'wpshadow_cleanup_snapshots' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_cleanup_snapshots' );
		}

		// Cleanup old annotations
		if ( ! wp_next_scheduled( 'wpshadow_cleanup_annotations' ) ) {
			wp_schedule_event( time(), 'weekly', 'wpshadow_cleanup_annotations' );
		}

		// Hook cleanup functions
		add_action( 'wpshadow_cleanup_exports', array( 'WPShadow\Reporting\Report_Export_Manager', 'cleanup_old_exports' ) );
		add_action( 'wpshadow_cleanup_snapshots', array( 'WPShadow\Reporting\Report_Snapshot_Manager', 'cleanup_old_snapshots' ) );
	}

	/**
	 * Register AJAX handlers
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	private static function register_ajax_handlers() {
		// AJAX handlers are registered in their own files via add_action
		// This method just ensures all handler files are loaded
		$ajax_handlers = array(
			'class-ajax-export-report.php',
			'class-ajax-save-snapshot.php',
			'class-ajax-compare-snapshots.php',
			'class-ajax-get-trend-data.php',
			'class-ajax-add-annotation.php',
			'class-ajax-send-integration.php',
			'class-ajax-calculate-analytics.php',
		);

		foreach ( $ajax_handlers as $handler_file ) {
			$file_path = WPSHADOW_PATH . 'includes/admin/ajax/' . $handler_file;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}

	/**
	 * Deactivation cleanup
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function deactivate() {
		// Clear scheduled cron jobs
		wp_clear_scheduled_hook( 'wpshadow_cleanup_exports' );
		wp_clear_scheduled_hook( 'wpshadow_cleanup_snapshots' );
		wp_clear_scheduled_hook( 'wpshadow_cleanup_annotations' );
	}

	/**
	 * Get integration settings
	 *
	 * @since 0.6093.1200
	 * @return array Integration configuration.
	 */
	public static function get_integration_settings() {
		return get_option( 'wpshadow_integrations', array(
			'slack_enabled'   => false,
			'slack_webhook'   => '',
			'teams_enabled'   => false,
			'teams_webhook'   => '',
			'webhook_enabled' => false,
			'webhook_url'     => '',
			'webhook_method'  => 'POST',
		) );
	}

	/**
	 * Save integration settings
	 *
	 * @since 0.6093.1200
	 * @param  array $settings Integration settings.
	 * @return bool Success.
	 */
	public static function save_integration_settings( $settings ) {
		$sanitized = array(
			'slack_enabled'   => isset( $settings['slack_enabled'] ) ? (bool) $settings['slack_enabled'] : false,
			'slack_webhook'   => isset( $settings['slack_webhook'] ) ? esc_url_raw( $settings['slack_webhook'] ) : '',
			'teams_enabled'   => isset( $settings['teams_enabled'] ) ? (bool) $settings['teams_enabled'] : false,
			'teams_webhook'   => isset( $settings['teams_webhook'] ) ? esc_url_raw( $settings['teams_webhook'] ) : '',
			'webhook_enabled' => isset( $settings['webhook_enabled'] ) ? (bool) $settings['webhook_enabled'] : false,
			'webhook_url'     => isset( $settings['webhook_url'] ) ? esc_url_raw( $settings['webhook_url'] ) : '',
			'webhook_method'  => isset( $settings['webhook_method'] ) ? sanitize_key( $settings['webhook_method'] ) : 'POST',
		);

		return update_option( 'wpshadow_integrations', $sanitized );
	}
}

// Initialize Phase 4
Phase4_Initializer::init();

// Register deactivation hook
register_deactivation_hook( WPSHADOW_BASENAME, array( 'WPShadow\Reporting\Phase4_Initializer', 'deactivate' ) );
