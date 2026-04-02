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

// Core finding operations
require_once $ajax_path . 'dismiss-finding-handler.php';
require_once $ajax_path . 'autofix-finding-handler.php';
require_once $ajax_path . 'dry-run-treatment-handler.php';
require_once $ajax_path . 'change-finding-status-handler.php';

// Post-scan treatment application
require_once $ajax_path . 'post-scan-treatments-handler.php';

// Dashboard operations
require_once $ajax_path . 'get-dashboard-data-handler.php';
require_once $ajax_path . 'save-dashboard-prefs-handler.php';
require_once $ajax_path . 'heartbeat-diagnostics-handler.php';

// Scanning operations
require_once $ajax_path . 'first-scan-handler.php';
require_once $ajax_path . 'deep-scan-handler.php';
require_once $ajax_path . 'dismiss-scan-notice-handler.php';

// Alerts
require_once $ajax_path . 'save-tagline-handler.php';

// Reporting
require_once $ajax_path . 'user-search-handler.php';

// Settings management
require_once $ajax_path . 'update-privacy-settings-handler.php';
require_once $ajax_path . 'update-data-retention-handler.php';
require_once $ajax_path . 'class-save-setting-handler.php';

// Activity tracking operations
require_once $ajax_path . 'class-get-activities-handler.php';

// Reports
require_once $ajax_path . 'class-site-dna-handler.php';
require_once $ajax_path . 'error-report-handler.php';

// Diagnostics & Treatments listing/toggles (Scan Settings UI)
require_once $ajax_path . 'class-ajax-diagnostics-list.php';
require_once $ajax_path . 'class-ajax-toggle-diagnostic.php';
require_once $ajax_path . 'class-ajax-set-diagnostic-frequency.php';
require_once $ajax_path . 'class-ajax-treatments-list.php';
require_once $ajax_path . 'class-ajax-toggle-treatment.php';
require_once $ajax_path . 'class-ajax-run-family-diagnostics.php';
require_once $ajax_path . 'class-ajax-run-single-diagnostic.php';
require_once $ajax_path . 'class-ajax-diagnostics-status.php';
require_once $ajax_path . 'class-ajax-last-family-results.php';

// Content review wizard
require_once $ajax_path . 'class-content-review-handlers.php';

// Register family diagnostics handler explicitly to guarantee availability.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\AJAX_Run_Family_Diagnostics' ) ) {
	\WPShadow\Admin\Ajax\AJAX_Run_Family_Diagnostics::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\AJAX_Run_Single_Diagnostic' ) ) {
	\WPShadow\Admin\Ajax\AJAX_Run_Single_Diagnostic::register();
}

// Register diagnostics status handler explicitly for live progress polling.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\AJAX_Diagnostics_Status' ) ) {
	\WPShadow\Admin\Ajax\AJAX_Diagnostics_Status::register();
}

// Register last family results handler for recovery fallback.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\AJAX_Last_Family_Results' ) ) {
	\WPShadow\Admin\Ajax\AJAX_Last_Family_Results::register();
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

