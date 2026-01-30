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
 * @since 1.2601.21
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ajax_path = __DIR__ . '/';

// Core finding operations
require_once $ajax_path . 'dismiss-finding-handler.php';
require_once $ajax_path . 'autofix-finding-handler.php';
require_once $ajax_path . 'dry-run-treatment-handler.php';
require_once $ajax_path . 'rollback-treatment-handler.php';
require_once $ajax_path . 'toggle-autofix-permission-handler.php';
require_once $ajax_path . 'allow-all-autofixes-handler.php';
require_once $ajax_path . 'change-finding-status-handler.php';

// Dashboard operations
require_once $ajax_path . 'get-dashboard-data-handler.php';
require_once $ajax_path . 'save-dashboard-prefs-handler.php';

// Scanning operations
require_once $ajax_path . 'first-scan-handler.php';
require_once $ajax_path . 'quick-scan-handler.php';
require_once $ajax_path . 'deep-scan-handler.php';
require_once $ajax_path . 'dismiss-scan-notice-handler.php';

// Notifications and alerts
require_once $ajax_path . 'save-tagline-handler.php';
require_once $ajax_path . 'mark-notification-read-handler.php';
require_once $ajax_path . 'clear-notifications-handler.php';

// Gamification
require_once $ajax_path . 'get-gamification-summary-handler.php';
require_once $ajax_path . 'get-leaderboard-handler.php';

// Reporting
require_once $ajax_path . 'generate-report-handler.php';
require_once $ajax_path . 'download-report-handler.php';
require_once $ajax_path . 'send-executive-report-handler.php';
require_once $ajax_path . 'export-csv-handler.php';

// Settings management
require_once $ajax_path . 'save-email-template-handler.php';
require_once $ajax_path . 'reset-email-template-handler.php';
require_once $ajax_path . 'update-report-schedule-handler.php';
require_once $ajax_path . 'update-privacy-settings-handler.php';
require_once $ajax_path . 'update-data-retention-handler.php';
require_once $ajax_path . 'update-scan-frequency-handler.php';

// Workflow operations
require_once $ajax_path . 'save-workflow-handler.php';
require_once $ajax_path . 'load-workflows-handler.php';
require_once $ajax_path . 'get-workflow-handler.php';
require_once $ajax_path . 'delete-workflow-handler.php';
require_once $ajax_path . 'toggle-workflow-handler.php';

// Activity tracking operations
require_once $ajax_path . 'class-get-activities-handler.php';
require_once $ajax_path . 'generate-workflow-name-handler.php';
require_once $ajax_path . 'get-available-actions-handler.php';
require_once $ajax_path . 'get-action-config-handler.php';
require_once $ajax_path . 'run-workflow-handler.php';
require_once $ajax_path . 'create-from-example-handler.php';
require_once $ajax_path . 'create-suggested-workflow-handler.php';
require_once $ajax_path . 'get-templates-handler.php';
require_once $ajax_path . 'create-from-template-handler.php';

// Email recipient management
require_once $ajax_path . 'add-email-recipient-handler.php';
require_once $ajax_path . 'approve-email-recipient-handler.php';
require_once $ajax_path . 'remove-email-recipient-handler.php';

// Guardian operations
require_once $ajax_path . 'toggle-guardian-handler.php';

// Off-peak scheduling
require_once $ajax_path . 'schedule-overnight-fix-handler.php';
require_once $ajax_path . 'schedule-offpeak-handler.php';

// Utilities
require_once $ajax_path . 'clear-cache-handler.php';
require_once $ajax_path . 'create-magic-link-handler.php';
require_once $ajax_path . 'revoke-magic-link-handler.php';
require_once $ajax_path . 'create-permanent-user-handler.php';
require_once $ajax_path . 'save-cache-options-handler.php';
require_once $ajax_path . 'mobile-check-handler.php';
require_once $ajax_path . 'a11y-audit-handler.php';
require_once $ajax_path . 'save-tip-prefs-handler.php';
require_once $ajax_path . 'dismiss-tip-handler.php';
require_once $ajax_path . 'check-broken-links-handler.php';
require_once $ajax_path . 'generate-password-handler.php';
require_once $ajax_path . 'consent-preferences-handler.php';
require_once $ajax_path . 'error-report-handler.php';
require_once $ajax_path . 'save-notification-rule-handler.php';
require_once $ajax_path . 'delete-notification-rule-handler.php';

// Onboarding operations
require_once $ajax_path . 'save-onboarding-handler.php';
require_once $ajax_path . 'skip-onboarding-handler.php';
require_once $ajax_path . 'dismiss-term-handler.php';
require_once $ajax_path . 'show-all-features-handler.php';
require_once $ajax_path . 'dismiss-graduation-handler.php';

// Timezone management
require_once $ajax_path . 'detect-timezone-handler.php';
require_once $ajax_path . 'set-timezone-handler.php';

// Visual comparison operations
require_once $ajax_path . 'get-visual-comparisons-handler.php';
require_once $ajax_path . 'get-visual-comparison-handler.php';

// Utilities operations
require_once $ajax_path . 'load-tool-handler.php';

// Exit interview operations
require_once $ajax_path . 'submit-exit-interview-handler.php';

// Kanban operations (loaded separately in kanban-module.php)
// - get-finding-family-handler.php
// - apply-family-fix-handler.php

// Diagnostics & Treatments listing/toggles (Scan Settings UI)
require_once $ajax_path . 'class-ajax-diagnostics-list.php';
require_once $ajax_path . 'class-ajax-toggle-diagnostic.php';
require_once $ajax_path . 'class-ajax-treatments-list.php';
require_once $ajax_path . 'class-ajax-toggle-treatment.php';

// Exit interview and followup operations
require_once $ajax_path . 'exit-followup-handlers.php';
// Test handler (temporary)
require_once $ajax_path . 'test-ajax-handler.php';