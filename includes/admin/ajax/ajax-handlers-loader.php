<?php
/**
 * AJAX Handlers Loader
 *
 * Loads all AJAX handler classes before they're registered in AJAX_Router.
 * Required because WordPress-style file naming (class-*-handler.php) doesn't match PSR-4 autoloading.
 *
 * @package WPShadow
 * @since 1.2601.21
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ajax_path = __DIR__ . '/';

// Core finding operations
require_once $ajax_path . 'class-dismiss-finding-handler.php';
require_once $ajax_path . 'class-autofix-finding-handler.php';
require_once $ajax_path . 'class-dry-run-treatment-handler.php';
require_once $ajax_path . 'class-rollback-treatment-handler.php';
require_once $ajax_path . 'class-toggle-autofix-permission-handler.php';
require_once $ajax_path . 'class-allow-all-autofixes-handler.php';
require_once $ajax_path . 'class-change-finding-status-handler.php';

// Dashboard operations
require_once $ajax_path . 'class-get-dashboard-data-handler.php';
require_once $ajax_path . 'class-save-dashboard-prefs-handler.php';

// Scanning operations
require_once $ajax_path . 'class-first-scan-handler.php';
require_once $ajax_path . 'class-quick-scan-handler.php';
require_once $ajax_path . 'class-deep-scan-handler.php';
require_once $ajax_path . 'class-dismiss-scan-notice-handler.php';

// Notifications and alerts
require_once $ajax_path . 'class-save-tagline-handler.php';
require_once $ajax_path . 'class-mark-notification-read-handler.php';
require_once $ajax_path . 'class-clear-notifications-handler.php';
require_once $ajax_path . 'class-update-notification-manager-handler.php';

// Gamification
require_once $ajax_path . 'class-get-gamification-summary-handler.php';
require_once $ajax_path . 'class-get-leaderboard-handler.php';

// Reporting
require_once $ajax_path . 'class-generate-report-handler.php';
require_once $ajax_path . 'class-download-report-handler.php';
require_once $ajax_path . 'class-send-executive-report-handler.php';
require_once $ajax_path . 'class-export-csv-handler.php';

// Settings management
require_once $ajax_path . 'class-save-email-template-handler.php';
require_once $ajax_path . 'class-reset-email-template-handler.php';
require_once $ajax_path . 'class-update-report-schedule-handler.php';
require_once $ajax_path . 'class-update-privacy-settings-handler.php';
require_once $ajax_path . 'class-update-data-retention-handler.php';
require_once $ajax_path . 'class-update-scan-frequency-handler.php';

// Workflow operations
require_once $ajax_path . 'class-save-workflow-handler.php';
require_once $ajax_path . 'class-load-workflows-handler.php';
require_once $ajax_path . 'class-get-workflow-handler.php';
require_once $ajax_path . 'class-delete-workflow-handler.php';
require_once $ajax_path . 'class-toggle-workflow-handler.php';
require_once $ajax_path . 'class-generate-workflow-name-handler.php';
require_once $ajax_path . 'class-get-available-actions-handler.php';
require_once $ajax_path . 'class-get-action-config-handler.php';
require_once $ajax_path . 'class-run-workflow-handler.php';
require_once $ajax_path . 'class-create-from-example-handler.php';
require_once $ajax_path . 'class-create-suggested-workflow-handler.php';
require_once $ajax_path . 'class-get-templates-handler.php';
require_once $ajax_path . 'class-create-from-template-handler.php';

// Email recipient management
require_once $ajax_path . 'class-add-email-recipient-handler.php';
require_once $ajax_path . 'class-approve-email-recipient-handler.php';
require_once $ajax_path . 'class-remove-email-recipient-handler.php';

// Guardian operations
require_once $ajax_path . 'class-toggle-guardian-handler.php';

// Off-peak scheduling
require_once $ajax_path . 'class-schedule-overnight-fix-handler.php';
require_once $ajax_path . 'class-schedule-offpeak-handler.php';

// Utilities
require_once $ajax_path . 'class-clear-cache-handler.php';
require_once $ajax_path . 'class-create-magic-link-handler.php';
require_once $ajax_path . 'class-revoke-magic-link-handler.php';
require_once $ajax_path . 'class-save-cache-options-handler.php';
require_once $ajax_path . 'class-mobile-check-handler.php';
require_once $ajax_path . 'class-save-tip-prefs-handler.php';
require_once $ajax_path . 'class-dismiss-tip-handler.php';
require_once $ajax_path . 'class-check-broken-links-handler.php';
require_once $ajax_path . 'class-generate-password-handler.php';
require_once $ajax_path . 'class-consent-preferences-handler.php';
require_once $ajax_path . 'class-error-report-handler.php';
require_once $ajax_path . 'class-save-notification-rule-handler.php';
require_once $ajax_path . 'class-delete-notification-rule-handler.php';

// Onboarding operations
require_once $ajax_path . 'class-save-onboarding-handler.php';
require_once $ajax_path . 'class-skip-onboarding-handler.php';
require_once $ajax_path . 'class-dismiss-term-handler.php';
require_once $ajax_path . 'class-show-all-features-handler.php';
require_once $ajax_path . 'class-dismiss-graduation-handler.php';

// Timezone management
require_once $ajax_path . 'class-detect-timezone-handler.php';
require_once $ajax_path . 'class-set-timezone-handler.php';

// Visual comparison operations
require_once $ajax_path . 'class-get-visual-comparisons-handler.php';
require_once $ajax_path . 'class-get-visual-comparison-handler.php';

// Kanban operations (loaded separately in kanban-module.php)
// - class-get-finding-family-handler.php
// - class-apply-family-fix-handler.php
