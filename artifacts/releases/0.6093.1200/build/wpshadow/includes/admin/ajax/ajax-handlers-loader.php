<?php
/**
 * AJAX Handlers Loader
 *
 * Loads all AJAX handler classes before they're registered in AJAX_Router.
 *
 * NOTE: With PSR-4 autoloading enabled, this file is no longer strictly necessary
 * as classes will be loaded automatically when referenced. However, we keep it for:
 * - Explicit dependency loading order
 * - Backwards compatibility
 * - Clear documentation of all AJAX handlers
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ajax_path = __DIR__ . '/';

if ( ! function_exists( 'wpshadow_require_ajax_handler' ) ) {
	/**
	 * Require an AJAX handler file when present.
	 *
	 * @param string $base_path Base AJAX handlers directory.
	 * @param string $file      Relative handler filename.
	 * @return void
	 */
	function wpshadow_require_ajax_handler( $base_path, $file ) {
		$path = $base_path . $file;

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
}

// Core finding operations.
wpshadow_require_ajax_handler( $ajax_path, 'autofix-finding-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dry-run-treatment-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'change-finding-status-handler.php' );

// Post-scan treatment application.
wpshadow_require_ajax_handler( $ajax_path, 'class-post-scan-treatments-handler.php' );

// Dashboard operations.
wpshadow_require_ajax_handler( $ajax_path, 'class-get-dashboard-data-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-save-dashboard-prefs-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'heartbeat-diagnostics-handler.php' );

// Scanning operations.
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-scan-notice-handler.php' );

// Notifications and alerts.
wpshadow_require_ajax_handler( $ajax_path, 'run-local-backup-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-restore-local-backup-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-download-local-backup-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-delete-local-backup-handler.php' );

// Reporting.
// Settings management.
wpshadow_require_ajax_handler( $ajax_path, 'class-save-setting-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-save-scan-config.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-save-diagnostic-frequency.php' );

// Activity tracking operations.
wpshadow_require_ajax_handler( $ajax_path, 'class-get-activities-handler.php' );
// Diagnostics & Treatments listing/toggles (Scan Settings UI).
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-diagnostics-list.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-toggle-diagnostic.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-set-diagnostic-frequency.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-treatments-list.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-toggle-treatment.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-save-treatment-inputs.php' );

// Governance & Readiness reporting.
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-readiness-inventory.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-readiness-export.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-environment-policy.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-treatment-maturity.php' );

// Register local backup handlers explicitly to guarantee admin-post availability.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Run_Local_Backup_Handler' ) ) {
	\WPShadow\Admin\Ajax\Run_Local_Backup_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Restore_Local_Backup_Handler' ) ) {
	\WPShadow\Admin\Ajax\Restore_Local_Backup_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Download_Local_Backup_Handler' ) ) {
	\WPShadow\Admin\Ajax\Download_Local_Backup_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Delete_Local_Backup_Handler' ) ) {
	\WPShadow\Admin\Ajax\Delete_Local_Backup_Handler::register();
}

// Register dashboard handlers explicitly for real-time updates.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Get_Dashboard_Data_Handler' ) ) {
	\WPShadow\Admin\Ajax\Get_Dashboard_Data_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Save_Dashboard_Prefs_Handler' ) ) {
	\WPShadow\Admin\Ajax\Save_Dashboard_Prefs_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Heartbeat_Diagnostics_Handler' ) ) {
	\WPShadow\Admin\Ajax\Heartbeat_Diagnostics_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Post_Scan_Treatments_Handler' ) ) {
	\WPShadow\Admin\Ajax\Post_Scan_Treatments_Handler::register();
}
