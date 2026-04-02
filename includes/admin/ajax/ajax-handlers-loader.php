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

// Core finding operations
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-finding-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'autofix-finding-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dry-run-treatment-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'rollback-treatment-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'toggle-autofix-permission-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'allow-all-autofixes-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'change-finding-status-handler.php' );

// Post-scan treatment application
wpshadow_require_ajax_handler( $ajax_path, 'post-scan-treatments-handler.php' );

// Dashboard operations
wpshadow_require_ajax_handler( $ajax_path, 'get-dashboard-data-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'save-dashboard-prefs-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'heartbeat-diagnostics-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'bulk-run-pending-diagnostics-handler.php' );

// Scanning operations
wpshadow_require_ajax_handler( $ajax_path, 'first-scan-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'quick-scan-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'deep-scan-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-scan-notice-handler.php' );

// Notifications and alerts
wpshadow_require_ajax_handler( $ajax_path, 'save-tagline-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'mark-notification-read-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'clear-notifications-handler.php' );

// Gamification
wpshadow_require_ajax_handler( $ajax_path, 'get-gamification-summary-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-leaderboard-handler.php' );

// Reporting
wpshadow_require_ajax_handler( $ajax_path, 'generate-report-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'download-report-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'send-executive-report-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'export-csv-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'user-search-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-run-privacy-report.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-refresh-privacy-reports.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-run-seo-report.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-refresh-seo-reports.php' );

// Settings management
wpshadow_require_ajax_handler( $ajax_path, 'save-email-template-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'reset-email-template-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'update-report-schedule-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'update-privacy-settings-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'update-data-retention-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'update-scan-frequency-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-save-setting-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-import-export-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-save-scan-config.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-save-diagnostic-frequency.php' );

// Workflow operations
wpshadow_require_ajax_handler( $ajax_path, 'save-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'load-workflows-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'delete-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'toggle-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'automations-dashboard-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-next-suggestion-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-workflow-diagnostic-search.php' );

// Activity tracking operations
wpshadow_require_ajax_handler( $ajax_path, 'class-get-activities-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'generate-workflow-name-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-available-actions-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-action-config-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'run-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'create-from-example-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'create-suggested-workflow-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-templates-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'create-from-template-handler.php' );

// Email recipient management
wpshadow_require_ajax_handler( $ajax_path, 'add-email-recipient-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'approve-email-recipient-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'remove-email-recipient-handler.php' );

// Guardian operations
wpshadow_require_ajax_handler( $ajax_path, 'toggle-guardian-handler.php' );

// Off-peak scheduling
wpshadow_require_ajax_handler( $ajax_path, 'schedule-overnight-fix-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'schedule-offpeak-handler.php' );


// Reports
wpshadow_require_ajax_handler( $ajax_path, 'class-site-dna-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'mobile-check-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'a11y-audit-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'save-tip-prefs-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-tip-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'check-broken-links-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'generate-password-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'consent-preferences-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'error-report-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'save-notification-rule-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'delete-notification-rule-handler.php' );

// Onboarding operations
wpshadow_require_ajax_handler( $ajax_path, 'save-onboarding-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'skip-onboarding-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-term-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'show-all-features-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'dismiss-graduation-handler.php' );

// Timezone management
wpshadow_require_ajax_handler( $ajax_path, 'detect-timezone-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'set-timezone-handler.php' );

// Visual comparison operations
wpshadow_require_ajax_handler( $ajax_path, 'get-visual-comparisons-handler.php' );
wpshadow_require_ajax_handler( $ajax_path, 'get-visual-comparison-handler.php' );

// Kanban operations (loaded separately in kanban-module.php)
// - get-finding-family-handler.php
// - apply-family-fix-handler.php
wpshadow_require_ajax_handler( $ajax_path, 'refresh-kanban-board-handler.php' );

// Diagnostics & Treatments listing/toggles (Scan Settings UI)
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-diagnostics-list.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-toggle-diagnostic.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-set-diagnostic-frequency.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-treatments-list.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-toggle-treatment.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-run-family-diagnostics.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-run-single-diagnostic.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-diagnostics-status.php' );
wpshadow_require_ajax_handler( $ajax_path, 'class-ajax-last-family-results.php' );

// Content review wizard
wpshadow_require_ajax_handler( $ajax_path, 'class-content-review-handlers.php' );

// Register consent handler (must be called explicitly)
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Consent_Preferences_Handler' ) ) {
	\WPShadow\Admin\Ajax\Consent_Preferences_Handler::register();
}

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

// Register privacy reports refresh handler for live list updates.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Refresh_Privacy_Reports_Handler' ) ) {
	\WPShadow\Admin\Ajax\Refresh_Privacy_Reports_Handler::register();
}

// Register privacy report runner for AJAX snapshot generation.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Run_Privacy_Report_Handler' ) ) {
	\WPShadow\Admin\Ajax\Run_Privacy_Report_Handler::register();

}

// Register SEO reports refresh handler for live list updates.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Refresh_SEO_Reports_Handler' ) ) {
	\WPShadow\Admin\Ajax\Refresh_SEO_Reports_Handler::register();
}

// Register SEO report runner for AJAX snapshot generation.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Run_SEO_Report_Handler' ) ) {
	\WPShadow\Admin\Ajax\Run_SEO_Report_Handler::register();
}

// Register quick scan handler explicitly for dashboard auto-scan reliability.
if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Quick_Scan_Handler' ) ) {
	\WPShadow\Admin\Ajax\Quick_Scan_Handler::register();
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

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Bulk_Run_Pending_Diagnostics_Handler' ) ) {
	\WPShadow\Admin\Ajax\Bulk_Run_Pending_Diagnostics_Handler::register();
}

if ( class_exists( '\\WPShadow\\Admin\\Ajax\\Post_Scan_Treatments_Handler' ) ) {
	\WPShadow\Admin\Ajax\Post_Scan_Treatments_Handler::register();
}

