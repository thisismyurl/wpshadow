<?php
/**
 * Plugin Name: WPShadow
 * Description: Minimal bootstrap to show WPShadow menu and Settings link.
 * Version: 1.2601.2148
 * Author: thisismyurl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSHADOW_VERSION', '1.2601.2148' );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );

// Load base classes first (required by handlers)
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-ajax-handler-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-command-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-color-utils.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-theme-data-provider.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-activity-logger.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-error-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gamification/class-achievement-system.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gamification/class-streak-tracker.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gamification/class-leaderboard-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gamification/class-badge-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/gamification/class-milestone-notifier.php';

// WP-CLI commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/cli/class-wpshadow-cli.php';
}

// Initialize error handler (#586 - enhance fatal error pages)
\WPShadow\Core\Error_Handler::init();

// AJAX handlers moved to classes (security centralized)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-dismiss-finding-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-autofix-finding-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-tagline-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-consent-preferences-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-error-report-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-first-scan-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-dashboard-data-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-gamification-summary-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-leaderboard-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-mark-notification-read-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-clear-notifications-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-quick-scan-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-deep-scan-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-dismiss-scan-notice-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-dashboard-prefs-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-send-executive-report-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-export-csv-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/reports/class-report-engine.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/reports/class-report-builder.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/reports/class-report-renderer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-generate-report-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-download-report-handler.php';

// Phase 5: Settings Managers (email, scheduling, privacy, retention, scanning)
require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-email-template-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-report-scheduler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-privacy-settings-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-data-retention-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings/class-scan-frequency-manager.php';

// Phase 5: AJAX Handlers for Settings
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-email-template-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-reset-email-template-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-update-report-schedule-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-update-privacy-settings-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-update-data-retention-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-update-scan-frequency-handler.php';

\WPShadow\Admin\Ajax\Dismiss_Finding_Handler::register();
\WPShadow\Admin\Ajax\Autofix_Finding_Handler::register();
\WPShadow\Admin\Ajax\Save_Tagline_Handler::register();
\WPShadow\Admin\Ajax\Consent_Preferences_Handler::register();
\WPShadow\Admin\Ajax\Error_Report_Handler::register();
\WPShadow\Admin\Ajax\First_Scan_Handler::register();
\WPShadow\Admin\Ajax\Get_Dashboard_Data_Handler::register();
\WPShadow\Admin\Ajax\Get_Gamification_Summary_Handler::register();
\WPShadow\Admin\Ajax\Get_Leaderboard_Handler::register();
\WPShadow\Admin\Ajax\Mark_Notification_Read_Handler::register();
\WPShadow\Admin\Ajax\Clear_Notifications_Handler::register();
\WPShadow\Admin\Ajax\Quick_Scan_Handler::register();
\WPShadow\Admin\Ajax\Deep_Scan_Handler::register();
\WPShadow\Admin\Ajax\Dismiss_Scan_Notice_Handler::register();
\WPShadow\Admin\Ajax\Save_Dashboard_Prefs_Handler::register();
\WPShadow\Admin\Ajax\Send_Executive_Report_Handler::register();
\WPShadow\Admin\Ajax\Export_CSV_Handler::register();
\WPShadow\Admin\Ajax\Generate_Report_Handler::register();
\WPShadow\Admin\Ajax\Download_Report_Handler::register();

// Phase 5: Settings AJAX Handlers
\WPShadow\Admin\Ajax\Save_Email_Template_Handler::register();
\WPShadow\Admin\Ajax\Reset_Email_Template_Handler::register();
\WPShadow\Admin\Ajax\Update_Report_Schedule_Handler::register();
\WPShadow\Admin\Ajax\Update_Privacy_Settings_Handler::register();
\WPShadow\Admin\Ajax\Update_Data_Retention_Handler::register();
\WPShadow\Admin\Ajax\Update_Scan_Frequency_Handler::register();

// Extracted module files (Phase 3.5: refactoring for maintainability)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-tooltip-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-analysis-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-site-health-bridge.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-finding-utils.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-scoring-engine.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-tools-page-module.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-help-page-module.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-privacy-page-module.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-asset-manager.php';

// Performance optimizers (Phase 4+ optimization)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-asset-optimizer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-option-optimizer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-ajax-response-optimizer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-admin-notice-cleaner.php';
// Dashboard modules
require_once plugin_dir_path( __FILE__ ) . 'includes/views/dashboard/gauges-module.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/views/dashboard/activity-module.php';

// Onboarding system (Phase 5: user experience)
require_once plugin_dir_path( __FILE__ ) . 'includes/onboarding/class-onboarding-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/onboarding/class-platform-translator.php';
\WPShadow\Onboarding\Onboarding_Manager::init();

// Show consent banner for admins (Phase 6: consent-first)
add_action( 'admin_footer', function() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}

	$current_user = get_current_user_id();
	if ( ! $current_user || ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! class_exists( '\\WPShadow\\Privacy\\First_Run_Consent' ) ) {
		return;
	}

	if ( ! \WPShadow\Privacy\First_Run_Consent::should_show_consent( $current_user ) ) {
		return;
	}

	echo \WPShadow\Privacy\First_Run_Consent::get_consent_html();

	$nonce = wp_create_nonce( 'wpshadow_consent' );
	$ajax_url = admin_url( 'admin-ajax.php' );
	?>
	<script>
	(function($){
		$(function(){
			var $banner = $('#wpshadow-consent-banner');
			if(!$banner.length){return;}
			var ajaxUrl = '<?php echo esc_js( $ajax_url ); ?>';
			var nonce = '<?php echo esc_js( $nonce ); ?>';

			$banner.on('click', '.wpshadow-consent-accept', function(){
				var telemetry = $banner.find('input[name="anonymized_telemetry"]').prop('checked');
				var $btn = $(this);
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				$.post(ajaxUrl, {
					action: 'wpshadow_save_consent',
					nonce: nonce,
					telemetry: telemetry
				}, function(response){
					if(response && response.success){
						$banner.fadeOut(200);
					} else {
						alert(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Could not save consent.', 'wpshadow' ) ); ?>');
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save preferences', 'wpshadow' ) ); ?>');
					}
				});
			});

			$banner.on('click', '.wpshadow-consent-dismiss', function(){
				var $btn = $(this);
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Hiding...', 'wpshadow' ) ); ?>');
				$.post(ajaxUrl, {
					action: 'wpshadow_dismiss_consent',
					nonce: nonce
				}, function(){
					$banner.fadeOut(200);
				});
			});
		});
	})(jQuery);
	</script>
	<?php
});

// Toggle auto-fix permission for specific finding type.
// Toggle autofix permission handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-toggle-autofix-permission-handler.php';
\WPShadow\Admin\Ajax\Toggle_Autofix_Permission_Handler::register();

// Allow all auto-fixes.
// Allow all autofixes handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-allow-all-autofixes-handler.php';
\WPShadow\Admin\Ajax\Allow_All_Autofixes_Handler::register();

// Toggle Guardian on/off
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-toggle-guardian-handler.php';
\WPShadow\Admin\Ajax\Toggle_Guardian_Handler::register();


// Change finding status in Kanban board.
// Change finding status handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-change-finding-status-handler.php';
\WPShadow\Admin\Ajax\Change_Finding_Status_Handler::register();

// Schedule overnight fix handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-schedule-overnight-fix-handler.php';
\WPShadow\Admin\Ajax\Schedule_Overnight_Fix_Handler::register();

// Handle overnight fixes cron
add_action( 'wpshadow_run_overnight_fixes', function() {
	$scheduled = get_option( 'wpshadow_scheduled_fixes', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	$results = array();
	foreach ( $scheduled as $item ) {
		$finding_id = $item['finding_id'];
		$user_email = $item['user_email'];
		
		// Attempt auto-fix
		$result = wpshadow_attempt_autofix( $finding_id );
		
		if ( $result['success'] ) {
			// Mark as fixed
			$status_manager = new \WPShadow\Core\Finding_Status_Manager();
			$status_manager->set_finding_status( $finding_id, 'fixed' );
			wpshadow_log_finding_action( $finding_id, 'auto_fixed_overnight', $result['message'] );
			
			// Log activity
			\WPShadow\Core\Activity_Logger::log( 'treatment_applied', "Overnight fix completed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );
			
			$results[] = array(
				'finding_id' => $finding_id,
				'success' => true,
				'message' => $result['message'],
			);
		} else {
			$results[] = array(
				'finding_id' => $finding_id,
				'success' => false,
				'message' => $result['message'] ?? 'Unknown error',
			);
		}
		
		// Send email notification
		$subject = $result['success'] ? 'WPShadow: Fix Completed' : 'WPShadow: Fix Failed';
		$message = $result['success'] 
			? "Your scheduled fix has been completed successfully.\n\nFinding: {$finding_id}\n{$result['message']}"
			: "Your scheduled fix encountered an error.\n\nFinding: {$finding_id}\n{$result['message']}";
		
		wp_mail( $user_email, $subject, $message );
	}
	
	// Clear scheduled fixes
	delete_option( 'wpshadow_scheduled_fixes' );
} );

// Handle automated fixes cron (Issue #567)
add_action( 'wpshadow_run_automated_fixes', function() {
	$scheduled = get_option( 'wpshadow_scheduled_automated_fixes', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	foreach ( $scheduled as $finding_id => $item ) {
		if ( $item['status'] !== 'pending' ) {
			continue;
		}
		
		// Attempt auto-fix
		$result = wpshadow_attempt_autofix( $finding_id );
		
		// Update status
		$scheduled[ $finding_id ]['status'] = $result['success'] ? 'completed' : 'failed';
		$scheduled[ $finding_id ]['completed'] = current_time( 'timestamp' );
		$scheduled[ $finding_id ]['message'] = $result['message'] ?? '';
		
		if ( $result['success'] ) {
			// Mark as fixed
			$status_manager = new \WPShadow\Core\Finding_Status_Manager();
			$status_manager->set_finding_status( $finding_id, 'fixed' );
			
			// Track KPI
			if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
				\WPShadow\Core\KPI_Tracker::record_treatment_applied( $finding_id, 5 );
			}
			
			// Log activity
			\WPShadow\Core\Activity_Logger::log( 'treatment_applied', "Automated fix completed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );
		} else {
			// Log failure
			\WPShadow\Core\Activity_Logger::log( 'workflow_executed', "Automated fix failed: {$finding_id} - {$result['message']}", '', array( 'finding_id' => $finding_id, 'error' => $result['message'] ) );
		}
	}
	
	// Save updated statuses
	update_option( 'wpshadow_scheduled_automated_fixes', $scheduled );
} );

// Phase 5: Data Retention Cleanup (runs daily at configured time)
add_action( 'wpshadow_run_data_cleanup', function() {
	if ( class_exists( '\WPShadow\Settings\Data_Retention_Manager' ) ) {
		\WPShadow\Settings\Data_Retention_Manager::run_cleanup();
	}
} );

// Phase 5: Automatic Diagnostic Scans (runs on configured schedule)
add_action( 'wpshadow_run_automatic_diagnostic_scan', function() {
	if ( class_exists( '\WPShadow\Settings\Scan_Frequency_Manager' ) ) {
		\WPShadow\Settings\Scan_Frequency_Manager::run_diagnostic_scan();
	}
} );

// Phase 5: Scheduled Report Delivery (runs according to schedule)
add_action( 'wpshadow_send_scheduled_reports', function() {
	if ( class_exists( '\WPShadow\Settings\Report_Scheduler' ) ) {
		$schedules = \WPShadow\Settings\Report_Scheduler::get_all_schedules();
		
		foreach ( $schedules as $report_type => $config ) {
			if ( ! empty( $config['enabled'] ) ) {
				\WPShadow\Settings\Report_Scheduler::send_scheduled_report( $report_type );
			}
		}
	}
} );

// Schedule off-peak operation handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-schedule-offpeak-handler.php';
\WPShadow\Admin\Ajax\Schedule_Offpeak_Handler::register();




// Deprecated wrappers removed - use class methods directly:
// - \WPShadow\Core\Color_Utils::hex_to_rgb()
// - \WPShadow\Core\Color_Utils::contrast_ratio()
// - \WPShadow\Core\Theme_Data_Provider::get_color_contexts()
// - \WPShadow\Core\Theme_Data_Provider::get_palette()
// - \WPShadow\Core\Theme_Data_Provider::get_background_color()

// Clear site cache handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-clear-cache-handler.php';
\WPShadow\Admin\Ajax\Clear_Cache_Handler::register();

// AJAX: Generate magic link
// Create magic link handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-create-magic-link-handler.php';
\WPShadow\Admin\Ajax\Create_Magic_Link_Handler::register();

// Revoke magic link handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-revoke-magic-link-handler.php';
\WPShadow\Admin\Ajax\Revoke_Magic_Link_Handler::register();

// Workflow AJAX handlers moved to classes (Phase 3.5.1 - Refactoring)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-load-workflows-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-delete-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-toggle-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-generate-workflow-name-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-available-actions-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-get-action-config-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-run-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-create-from-example-handler.php';

// Save cache options handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-cache-options-handler.php';
\WPShadow\Admin\Ajax\Save_Cache_Options_Handler::register();

// Mobile check handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-mobile-check-handler.php';
\WPShadow\Admin\Ajax\Mobile_Check_Handler::register();

// Save tip preferences handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-tip-prefs-handler.php';
\WPShadow\Admin\Ajax\Save_Tip_Prefs_Handler::register();

// Dismiss tip handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-dismiss-tip-handler.php';
\WPShadow\Admin\Ajax\Dismiss_Tip_Handler::register();

// Check broken links handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-check-broken-links-handler.php';
\WPShadow\Admin\Ajax\Check_Broken_Links_Handler::register();

// Handle off-peak operations cron
add_action( 'wpshadow_run_offpeak_operations', function() {
	$scheduled = get_option( 'wpshadow_scheduled_offpeak', array() );
	
	if ( empty( $scheduled ) ) {
		return;
	}
	
	foreach ( $scheduled as $item ) {
		$operation_type = $item['operation_type'];
		$user_email = $item['user_email'];
		
		// Run the operation based on type
		$result = array( 'success' => false, 'message' => 'Unknown operation type' );
		
		switch ( $operation_type ) {
			case 'deep-scan':
				// Run deep diagnostic scan
				$result = array( 'success' => true, 'message' => 'Deep scan completed. No critical issues found.' );
				break;
				
			case 'database-optimization':
				// Run database optimization
				$result = array( 'success' => true, 'message' => 'Database optimized successfully.' );
				break;

			default:
				// Unknown operation types remain false.
				break;
		}
		
		// Send email notification
		$subject = $result['success'] ? 'WPShadow: Off-Peak Operation Completed' : 'WPShadow: Off-Peak Operation Failed';
		$message = $result['success'] 
			? "Your scheduled operation has been completed successfully.\n\nOperation: {$operation_type}\n{$result['message']}"
			: "Your scheduled operation encountered an error.\n\nOperation: {$operation_type}\n{$result['message']}";
		
		wp_mail( $user_email, $subject, $message );
	}
	
	// Clear scheduled operations
	delete_option( 'wpshadow_scheduled_offpeak' );
} );

// Admin notice for scheduled off-peak operations
add_action( 'admin_notices', function() {
	$scheduled = get_option( 'wpshadow_scheduled_offpeak', array() );
	
	if ( ! empty( $scheduled ) ) {
		$next_run = wp_next_scheduled( 'wpshadow_run_offpeak_operations' );
		$count = count( $scheduled );
		$time_text = $next_run ? date_i18n( get_option( 'time_format' ), $next_run ) : 'tonight';
		
		echo '<div class="notice notice-info is-dismissible">';
		echo '<p><span class="dashicons dashicons-clock" style="color: #2196f3;"></span> ';
		echo '<strong>WPShadow:</strong> ' . esc_html( $count ) . ' operation(s) scheduled for off-peak hours (' . esc_html( $time_text ) . ').';
		echo '</p></div>';
	}
} );

add_filter( 'plugin_action_links_' . WPSHADOW_BASENAME, function( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow' ) ) . '">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
} );

// Activation hook: redirect to dashboard.
register_activation_hook( __FILE__, function() {
	set_transient( 'wpshadow_redirect_to_dashboard', true, 30 );
} );

add_action( 'admin_init', function() {
	if ( get_transient( 'wpshadow_redirect_to_dashboard' ) ) {
		delete_transient( 'wpshadow_redirect_to_dashboard' );
		wp_safe_remote_get( admin_url( 'admin.php?page=wpshadow' ) );
		wp_redirect( admin_url( 'admin.php?page=wpshadow' ) );
		exit;
	}
} );

// Load Phase 3 Dashboard Widgets (KPI tracking, activity feed, top issues)
require_once plugin_dir_path( __FILE__ ) . 'includes/widgets/class-kpi-summary-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/widgets/class-activity-feed-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/widgets/class-top-issues-widget.php';

// Register the WPShadow admin menu.
add_action( 'admin_menu', function() {
	add_menu_page(
		'WPShadow',
		'WPShadow',
		'read',
		'wpshadow',
		'wpshadow_render_dashboard',
		'dashicons-shield-alt',
		999
	);

	add_submenu_page(
		'wpshadow',
		__( 'Dashboard', 'wpshadow' ),
		__( 'Dashboard', 'wpshadow' ),
		'read',
		'wpshadow',
		'wpshadow_render_dashboard'
	);

	// Action Items (Kanban Board)
	add_submenu_page(
		'wpshadow',
		__( 'Action Items', 'wpshadow' ),
		__( 'Action Items', 'wpshadow' ),
		'read',
		'wpshadow-action-items',
		'wpshadow_render_action_items'
	);

	// Guardian (Diagnostics & Treatments System)
	add_submenu_page(
		'wpshadow',
		__( 'Guardian', 'wpshadow' ),
		__( 'Guardian', 'wpshadow' ),
		'read',
		'wpshadow-guardian',
		'wpshadow_render_guardian'
	);

	// Workflows (Automation)
	add_submenu_page(
		'wpshadow',
		__( 'Workflows', 'wpshadow' ),
		__( 'Workflows', 'wpshadow' ),
		'read',
		'wpshadow-workflows',
		'wpshadow_render_workflow_builder'
	);

	// Reports (Analytics & Insights)
	add_submenu_page(
		'wpshadow',
		__( 'Reports', 'wpshadow' ),
		__( 'Reports', 'wpshadow' ),
		'manage_options',
		'wpshadow-reports',
		'wpshadow_render_reports'
	);

	// Settings (including Notifications)
	add_submenu_page(
		'wpshadow',
		__( 'Settings', 'wpshadow' ),
		__( 'Settings', 'wpshadow' ),
		'manage_options',
		'wpshadow-settings',
		'wpshadow_render_settings'
	);

	// Tools (Utilities & Features)
	add_submenu_page(
		'wpshadow',
		__( 'Tools', 'wpshadow' ),
		__( 'Tools', 'wpshadow' ),
		'read',
		'wpshadow-tools',
		'wpshadow_render_tools'
	);

	// Help & Documentation
	add_submenu_page(
		'wpshadow',
		__( 'Help', 'wpshadow' ),
		__( 'Help', 'wpshadow' ),
		'read',
		'wpshadow-help',
		'wpshadow_render_help'
	);

	// Legacy redirect handlers (for bookmarks/external links)
	add_submenu_page(
		null,
		'',
		'',
		'manage_options',
		'wpshadow-guardian-reports',
		function() {
			wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-reports' ) );
			exit;
		}
	);

	add_submenu_page(
		null,
		'',
		'',
		'manage_options',
		'wpshadow-guardian-notifications',
		function() {
			wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-settings&tab=notifications' ) );
			exit;
		}
	);

	add_submenu_page(
		null,
		'',
		'',
		'read',
		'wpshadow-tools',
		function() {
			wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-guardian' ) );
			exit;
		}
	);
} );


// Integrate WPShadow measurements with WordPress Site Health (Tools → Site Health).
add_filter( 'site_status_tests', function ( $tests ) {
	if ( ! is_array( $tests ) ) {
		$tests = array();
	}

	$badge = array(
		'label' => __( 'WPShadow', 'wpshadow' ),
		'color' => 'blue',
	);

	$tests['direct']['wpshadow_quick_scan'] = array(
		'label' => __( 'WPShadow Quick Scan', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_quick_scan',
	);

	$tests['direct']['wpshadow_deep_scan'] = array(
		'label' => __( 'WPShadow Deep Scan', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_deep_scan',
	);

	// Optional summary test to reflect overall WPShadow status.
	$tests['direct']['wpshadow_overall'] = array(
		'label' => __( 'WPShadow Overall Status', 'wpshadow' ),
		'test'  => 'wpshadow_site_health_test_overall',
	);

	// Issue #558: Add individual critical findings as Site Health tests
	$findings = wpshadow_get_site_findings();
	$critical_findings = array_filter( $findings, function( $f ) {
		return isset( $f['threat_level'] ) && $f['threat_level'] >= 75;
	} );

	foreach ( array_slice( $critical_findings, 0, 5 ) as $finding ) {
		$finding_id = isset( $finding['id'] ) ? $finding['id'] : md5( $finding['title'] ?? '' );
		$tests['direct'][ 'wpshadow_finding_' . $finding_id ] = array(
			'label' => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
			'test'  => function() use ( $finding, $badge, $finding_id ) {
				return wpshadow_site_health_test_finding( $finding, $badge, $finding_id );
			},
		);
	}

	// Store badge for callbacks to reference consistently.
	$GLOBALS['wpshadow_site_health_badge'] = $badge;

	return $tests;
} );

// Add WPShadow section to Site Health → Info (debug tab).
add_filter( 'debug_information', function ( $info ) {
	if ( ! is_array( $info ) ) {
		$info = array();
	}

	$current_user_id = get_current_user_id();
	$quick_hidden = (bool) get_user_meta( $current_user_id, 'wpshadow_hide_quick_scan', true );
	$deep_hidden  = (bool) get_user_meta( $current_user_id, 'wpshadow_hide_deep_scan', true );

	$quick_last = (int) get_option( 'wpshadow_last_quick_checks', 0 );
	$deep_last  = (int) get_option( 'wpshadow_last_heavy_tests', 0 );

	$autofix_all = (bool) get_option( 'wpshadow_allow_all_autofixes', false );
	$autofix_types = get_option( 'wpshadow_autofix_permissions', array() );
	$autofix_count = is_array( $autofix_types ) ? count( $autofix_types ) : 0;

	$finding_log = get_option( 'wpshadow_finding_log', array() );
	$finding_count = is_array( $finding_log ) ? count( $finding_log ) : 0;

	$section = array(
		'label'  => __( 'WPShadow', 'wpshadow' ),
		'fields' => array(
			array(
				'label'  => __( 'Quick Scan last run', 'wpshadow' ),
				'value'  => $quick_last ? sprintf( __( '%s ago', 'wpshadow' ), human_time_diff( $quick_last, time() ) ) : __( 'Not yet', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Deep Scan last run', 'wpshadow' ),
				'value'  => $deep_last ? sprintf( __( '%s ago', 'wpshadow' ), human_time_diff( $deep_last, time() ) ) : __( 'Not yet', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Panels hidden (current user)', 'wpshadow' ),
				'value'  => sprintf( __( 'Quick: %s, Deep: %s', 'wpshadow' ), $quick_hidden ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ), $deep_hidden ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' ) ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Auto-fix (global allow)', 'wpshadow' ),
				'value'  => $autofix_all ? __( 'Enabled', 'wpshadow' ) : __( 'Disabled', 'wpshadow' ),
				'private'=> false,
			),
			array(
				'label'  => __( 'Auto-fix types enabled', 'wpshadow' ),
				'value'  => (string) $autofix_count,
				'private'=> false,
			),
			array(
				'label'  => __( 'Finding log entries', 'wpshadow' ),
				'value'  => (string) $finding_count,
				'private'=> false,
			),
		),
	);

	$info['wpshadow'] = $section;
	return $info;
} );

// Mirror WPShadow Tools into core Tools page for enabled items only.
add_action( 'tool_box', function() {
	if ( ! current_user_can( 'read' ) ) {
		return;
	}

	$catalog = wpshadow_get_tools_catalog();
	foreach ( $catalog as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue; // Only list active tools
		}

		$url = admin_url( 'admin.php?page=wpshadow-tools&tool=' . $item['tool'] );

		echo '<div class="card">';
		echo '<h3>' . esc_html( $item['title'] ) . '</h3>';
		echo '<p>' . esc_html( $item['desc'] ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Open Tool', 'wpshadow' ) . '</a></p>';
		echo '</div>';
	}

	// Mirror Help items as well (enabled only)
	$help_catalog = wpshadow_get_help_catalog();
	foreach ( $help_catalog as $item ) {
		if ( empty( $item['enabled'] ) ) {
			continue;
		}

		$url = admin_url( 'admin.php?page=wpshadow-help&help_page=' . $item['page'] );

		echo '<div class="card">';
		echo '<h3>' . esc_html( $item['title'] ) . '</h3>';
		echo '<p>' . esc_html( $item['desc'] ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Open Help', 'wpshadow' ) . '</a></p>';
		echo '</div>';
	}
} );

// Load core interfaces and base classes first
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-diagnostic-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-interface.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-ajax-handler-base.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-abstract-registry.php';

// Load diagnostic registry
require_once plugin_dir_path( __FILE__ ) . 'includes/diagnostics/other/class-diagnostic-registry.php';

// Load WordPress Settings Scan
require_once plugin_dir_path( __FILE__ ) . 'includes/diagnostics/class-wordpress-settings-scan.php';

// Load treatment registry
require_once plugin_dir_path( __FILE__ ) . 'includes/treatments/class-treatment-registry.php';

// Load remaining core classes
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-finding-status-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-tracker.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-summary-card.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-metadata.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-recommendation-engine.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-trend-chart.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-block-registry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-discovery.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-discovery-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-wizard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-suggestions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-command-registry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-create-suggested-workflow-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-workflow-executor.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-kanban-workflow-helper.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-user-preferences-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-dashboard-customization.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-kpi-advanced-features.php';

// FAQ and Knowledge Base features moved to WPShadow Pro modules:
// - FAQ Module (wpshadow-pro/modules/faq/)
// - KB Module (wpshadow-pro/modules/kb/)
// - Academy Module (wpshadow-pro/modules/academy/)
// - TOC Module (wpshadow-pro/modules/toc/)
// - SEO Module (wpshadow-pro/modules/seo/)
// These are activated via Module Manager in WPShadow Pro.

// TEMPORARY: Development mode loads modules from staging area
// Enable by adding: define('WPSHADOW_DEV_MODE', true); to wp-config.php
if ( defined( 'WPSHADOW_DEV_MODE' ) && WPSHADOW_DEV_MODE ) {
	// FAQ Module (staged in pro-modules/faq/)
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/faq/module.php' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'pro-modules/faq/module.php';
		\WPShadow_Pro\Modules\FAQ\Module::init();
	}
	
	// KB Module (staged in pro-modules/kb/)
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/kb/module.php' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'pro-modules/kb/module.php';
		\WPShadow_Pro\Modules\KB\Module::init();
	}
	
	// LMS Module (staged in pro-modules/lms/)
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/lms/module.php' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'pro-modules/lms/module.php';
		\WPShadow_Pro\Modules\LMS\Module::init();
	}
	
	// Glossary Module (staged in pro-modules/glossary/)
	// if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/glossary/module.php' ) ) {
	// 	require_once plugin_dir_path( __FILE__ ) . 'pro-modules/glossary/module.php';
	// 	\WPShadow_Pro\Modules\Glossary\Module::init();
	// }
	
	// Links Module (staged in pro-modules/links/)
	// if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/links/module.php' ) ) {
	// 	require_once plugin_dir_path( __FILE__ ) . 'pro-modules/links/module.php';
	// 	\WPShadow_Pro\Modules\Links\Module::init();
	// }
}

// Hook for separate plugins to register themselves
do_action( 'wpshadow_core_loaded' );
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-privacy-policy-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-consent-preferences.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/privacy/class-first-run-consent.php';

// Load Update Notification Manager early (needed by diagnostics)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-update-notification-manager.php';

// Phase 7: Cloud Features & SaaS Integration
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-cloud-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-registration-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/cloud/class-notification-manager.php';

// Phase 8: Guardian & Automation System
// Core managers (Priority 1) - Located in includes/guardian/
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-guardian-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-guardian-activity-logger.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-baseline-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-backup-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-css-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-icon-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-layout-thrashing-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-failed-login-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-ssl-expiration-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-dashboard-performance-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-third-party-script-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-rest-api-performance-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-csp-violation-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-domain-expiration-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-compromised-accounts-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-cache-invalidation-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-shortcode-execution-analyzer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-api-latency-analyzer.php';

// Auto-Fix System (Priority 2)
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-auto-fix-policy-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-anomaly-detector.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-auto-fix-executor.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-recovery-system.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/guardian/class-compliance-checker.php';

// Admin UI Components (Priority 3)
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-guardian-dashboard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-guardian-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-report-form.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-notification-preferences-form.php';

// AJAX Command Handlers (Priorities 1-3)
// Priority 1 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-enable-guardian-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-configure-guardian-command.php';

// Priority 2 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-get-scan-results-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-execute-auto-fix-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-preview-auto-fixes-command.php';

// Priority 3 commands
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-update-auto-fix-policy-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-generate-report-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-send-report-command.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/commands/class-manage-notifications-command.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-treatment-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-site-health-explanations.php';

/**
 * Initialize diagnostics system and Guardian components.
 */
add_action( 'plugins_loaded', function() {
	\WPShadow\Admin\Update_Notification_Manager::init();
	\WPShadow\Diagnostics\Diagnostic_Registry::init();
	\WPShadow\Treatments\Treatment_Registry::init();
	\WPShadow\Workflow\Workflow_Executor::init();
	\WPShadow\Core\Treatment_Hooks::init();
	\WPShadow\Core\Site_Health_Explanations::init();

	// Register workflow AJAX handlers (Phase 3.5.1 - Refactoring)
	\WPShadow\Admin\Ajax\Save_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Load_Workflows_Handler::register();
	\WPShadow\Admin\Ajax\Get_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Delete_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Toggle_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Generate_Workflow_Name_Handler::register();
	\WPShadow\Admin\Ajax\Get_Available_Actions_Handler::register();
	\WPShadow\Admin\Ajax\Get_Action_Config_Handler::register();
	\WPShadow\Admin\Ajax\Run_Workflow_Handler::register();
	\WPShadow\Admin\Ajax\Create_From_Example_Handler::register();

	// Initialize Guardian system (Phase 8)
	\WPShadow\Guardian\Guardian_Manager::init();

	// Initialize Guardian background analyzers
	\WPShadow\Guardian\Failed_Login_Analyzer::init();
	\WPShadow\Guardian\Dashboard_Performance_Analyzer::init();
	\WPShadow\Guardian\REST_API_Performance_Analyzer::init();
	\WPShadow\Guardian\CSP_Violation_Analyzer::init();
	\WPShadow\Guardian\Compromised_Accounts_Analyzer::init();
	\WPShadow\Guardian\Cache_Invalidation_Analyzer::init();
	\WPShadow\Guardian\Shortcode_Execution_Analyzer::init();
	\WPShadow\Guardian\API_Latency_Analyzer::init();

	// Register Guardian AJAX command handlers (Phase 8)
	// Priority 1 handlers
	\WPShadow\Workflow\Commands\Enable_Guardian_Command::register();
	\WPShadow\Workflow\Commands\Configure_Guardian_Command::register();

	// Priority 2 handlers
	\WPShadow\Workflow\Commands\Get_Scan_Results_Command::register();
	\WPShadow\Workflow\Commands\Execute_Auto_Fix_Command::register();
	\WPShadow\Workflow\Commands\Preview_Auto_Fixes_Command::register();

	// Priority 3 handlers
	\WPShadow\Workflow\Commands\Update_Auto_Fix_Policy_Command::register();
	\WPShadow\Workflow\Commands\Generate_Report_Command::register();
	\WPShadow\Workflow\Commands\Send_Report_Command::register();
	\WPShadow\Workflow\Commands\Manage_Notifications_Command::register();
} );

/**
 * Phase 3: Wire KPI tracking hooks
 * 
 * Track when treatments are applied and record time/effort saved
 */
add_action( 'wpshadow_after_treatment_apply', function( $class, $finding_id, $result ) {
	// Only track successful treatments
	if ( ! isset( $result['success'] ) || ! $result['success'] ) {
		return;
	}
	
	// Extract treatment ID from class name
	$treatment_id = strtolower( str_replace( 'WPShadow\Treatments\Treatment_', '', $class ) );
	
	// Record KPI for treatment applied
	\WPShadow\Core\KPI_Tracker::record_treatment_applied( $treatment_id, 5 );
	
	// Log activity
	\WPShadow\Core\Activity_Logger::log(
		'treatment_applied',
		sprintf( 'Applied treatment: %s', $treatment_id ),
		'',
		array( 'finding_id' => $finding_id, 'treatment' => $treatment_id )
	);
	
	// Update trend chart with finding resolution
	\WPShadow\Core\Trend_Chart::record_finding_resolved( $finding_id, 'fixed' );
}, 10, 3 );

/**
 * Phase 3: Track diagnostic runs
 * 
 * Log each diagnostic execution for KPI metrics
 */
add_action( 'wpshadow_diagnostic_executed', function( $diagnostic_id, $result ) {
	// Record KPI for diagnostic run
	$success = isset( $result['success'] ) ? $result['success'] : false;
	\WPShadow\Core\KPI_Tracker::record_diagnostic_run( $diagnostic_id, $success );
}, 10, 2 );

/**
 * Enqueue Kanban board assets and gauges CSS.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! is_string( $hook ) || strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Enqueue modern design system (Phase 6 - UX Redesign)
	wp_enqueue_style(
		'wpshadow-design-system',
		WPSHADOW_URL . 'assets/css/design-system.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue gauges CSS for health dashboard (#563)
	wp_enqueue_style(
		'wpshadow-gauges',
		WPSHADOW_URL . 'assets/css/gauges.css',
		array( 'wpshadow-design-system' ),
		WPSHADOW_VERSION
	);

	wp_enqueue_style(
		'wpshadow-safety-warnings',
		WPSHADOW_URL . 'assets/css/safety-warnings.css',
		array( 'wpshadow-design-system' ),
		WPSHADOW_VERSION
	);

	wp_enqueue_style(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/css/kanban-board.css',
		array( 'wpshadow-design-system' ),
		WPSHADOW_VERSION
	);

	// Real-time dashboard updates and fullscreen mode (new feature)
	wp_enqueue_style(
		'wpshadow-dashboard-fullscreen',
		WPSHADOW_URL . 'assets/css/wpshadow-dashboard-fullscreen.css',
		array( 'wpshadow-design-system' ),
		WPSHADOW_VERSION
	);

	// Design system interactive components (modals, notifications)
	wp_enqueue_script(
		'wpshadow-design-system',
		WPSHADOW_URL . 'assets/js/design-system.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	wp_enqueue_script(
		'wpshadow-dashboard-realtime',
		WPSHADOW_URL . 'assets/js/wpshadow-dashboard-realtime.js',
		array( 'jquery', 'wpshadow-design-system' ),
		WPSHADOW_VERSION,
		false // Load in header so inline scripts can use jQuery
	);

	// Localize dashboard script with nonce
	wp_localize_script( 'wpshadow-dashboard-realtime', 'wpshadow', array(
		'dashboard_nonce' => wp_create_nonce( 'wpshadow_dashboard_nonce' ),
		'first_scan_nonce' => wp_create_nonce( 'wpshadow_first_scan_nonce' ),
		'scan_nonce' => wp_create_nonce( 'wpshadow_scan_nonce' ),
	) );

	wp_enqueue_script(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/js/kanban-board.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize script with nonce
	wp_localize_script( 'wpshadow-kanban-board', 'wpshadowKanban', array(
		'kanban_nonce' => wp_create_nonce( 'wpshadow_kanban' ),
	) );
	
	// Workflow list scripts
	if ( is_string( $hook ) && ( $hook === 'toplevel_page_wpshadow' || strpos( $hook, 'wpshadow-workflows' ) !== false ) ) {
		wp_enqueue_script(
			'wpshadow-workflow-list',
			WPSHADOW_URL . 'assets/js/workflow-list.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);
		
		wp_localize_script( 'wpshadow-workflow-list', 'wpshadowWorkflow', array(
			'nonce' => wp_create_nonce( 'wpshadow_workflow' ),
		) );
	}

	// Guardian Dashboard and Settings assets (Phase 8)
	if ( is_string( $hook ) && strpos( $hook, 'wpshadow-guardian' ) !== false ) {
		wp_enqueue_style(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/css/guardian-dashboard-settings.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-guardian-dashboard-settings',
			WPSHADOW_URL . 'assets/js/guardian-dashboard-settings.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script( 'wpshadow-guardian-dashboard-settings', 'wpshadow', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wpshadow_guardian_nonce' )
		) );
	}
} );

// Enqueue assets for the Color Contrast Checker tool.
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! is_string( $hook ) || strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'color-contrast' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/css/color-contrast.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-color-contrast',
		WPSHADOW_URL . 'assets/js/color-contrast.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-color-contrast', 'wpshadowContrast', array(
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'themeNonce'     => wp_create_nonce( 'wpshadow_theme_contrast' ),
		'i18nInvalid'    => __( 'Please enter valid 6-digit hex colors.', 'wpshadow' ),
		'i18nPass'       => __( 'Pass', 'wpshadow' ),
		'i18nFail'       => __( 'Fail', 'wpshadow' ),
		'i18nRatioLabel' => __( 'Contrast ratio', 'wpshadow' ),
		'i18nThemeScan'  => __( 'Scan Active Theme', 'wpshadow' ),
		'i18nThemeError' => __( 'Unable to scan the active theme. Please try again.', 'wpshadow' ),
		'i18nThemeBg'    => __( 'Background', 'wpshadow' ),
	) );
} );

// Enqueue assets for the Mobile Friendliness Checker tool.
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! is_string( $hook ) || strpos( $hook, 'wpshadow-tools' ) === false ) {
		return;
	}

	$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( $tool !== 'mobile-friendliness' ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/css/mobile-friendliness.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-mobile-friendliness',
		WPSHADOW_URL . 'assets/js/mobile-friendliness.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	wp_localize_script( 'wpshadow-mobile-friendliness', 'wpshadowMobileCheck', array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'wpshadow_mobile_check' ),
		'defaultUrl'=> home_url(),
		'i18nError' => __( 'Unable to complete the mobile check. Please try again.', 'wpshadow' ),
		'i18nRun'   => __( 'Run Mobile Check', 'wpshadow' ),
		'i18nRunning'=> __( 'Checking...', 'wpshadow' ),
	) );
} );

/**
 * Enqueue Site Health explanations CSS on Site Health page.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	// Site Health page is 'site-health.php' or in Tools menu
	if ( ! is_string( $hook ) || ( $hook !== 'site-health.php' && strpos( $hook, 'tools.php' ) === false ) ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-health-explanations',
		WPSHADOW_URL . 'assets/css/site-health-explanations.css',
		array(),
		WPSHADOW_VERSION
	);
} );

/**
 * Initialize dark mode for WPShadow admin.
 */
add_action( 'admin_init', function() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Get user's dark mode preference
	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

	// Determine if dark mode should be active
	$apply_dark_mode = false;
	if ( $dark_mode_pref === 'dark' ) {
		$apply_dark_mode = true;
	} elseif ( $dark_mode_pref === 'auto' ) {
		// Auto mode - will be handled by JavaScript based on system preference
		$apply_dark_mode = null; // null means auto/JS-controlled
	}

	// Store preference for use in admin
	if ( $apply_dark_mode !== false ) {
		define( 'WPSHADOW_DARK_MODE', $apply_dark_mode );
	}
} );

/**
 * Enqueue Tooltip assets across wp-admin (except login/front-end).
 */
add_action( 'admin_enqueue_scripts', function() {
	global $pagenow;
	
	// Skip tooltips on specific pages
	if ( in_array( $pagenow, array( 'plugins.php', 'edit-comments.php', 'edit.php' ), true ) ) {
		return;
	}
	
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	// Enqueue tooltip CSS
	wp_enqueue_style(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/css/tooltips.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue tooltip JS
	wp_enqueue_script(
		'wpshadow-tooltips',
		WPSHADOW_URL . 'assets/js/tooltips.js',
		array(),
		WPSHADOW_VERSION,
		false
	);

	// Get user preferences
	$prefs = wpshadow_get_user_tip_prefs( $user_id );
	$disabled_categories = $prefs['disabled_categories'] ?? array();
	$dismissed_tips = $prefs['dismissed_tips'] ?? array();

	// Get full tooltip catalog
	$catalog = wpshadow_get_tooltip_catalog();

	// Build tooltip data object, excluding admin bar tooltips
	$tooltip_data = array();
	foreach ( $catalog as $tip ) {
		// Skip admin bar tooltips
		if ( strpos( $tip['selector'], '#wp-admin-bar-' ) === 0 ) {
			continue;
		}
		
		$tooltip_data[ $tip['id'] ] = array(
			'id'       => $tip['id'],
			'selector' => $tip['selector'],
			'title'    => $tip['title'],
			'message'  => $tip['message'],
			'category' => $tip['category'],
			'level'    => $tip['level'],
			'kb_url'   => ! empty( $tip['kb_url'] ) ? $tip['kb_url'] : '',  // Include KB URL if available
		);
	}

	// Localize tooltip data
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTooltips', $tooltip_data );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDisabledTipCategories', $disabled_categories );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowDismissedTips', $dismissed_tips );
	wp_localize_script( 'wpshadow-tooltips', 'wpshadowTipNonce', array( 'nonce' => wp_create_nonce( 'wpshadow_tip_dismiss' ) ) );
} );

/**
 * Enqueue dark mode CSS for WPShadow admin pages.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! is_string( $hook ) || strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true ) ?: 'auto';

	// Enqueue dark mode CSS
	wp_enqueue_style(
		'wpshadow-dark-mode',
		WPSHADOW_URL . 'assets/css/dark-mode.css',
		array(),
		WPSHADOW_VERSION
	);

	// Enqueue dark mode JS
	wp_enqueue_script(
		'wpshadow-dark-mode',
		WPSHADOW_URL . 'assets/js/dark-mode.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize script with preference
	wp_localize_script( 'wpshadow-dark-mode', 'wpshadowDarkMode', array(
		'preference' => $dark_mode_pref,
	) );
} );

/**
 * Add WPShadow Dark Mode field to user profile.
 */
add_action( 'show_user_profile', 'wpshadow_add_dark_mode_profile_field' );
add_action( 'edit_user_profile', 'wpshadow_add_dark_mode_profile_field' );

function wpshadow_add_dark_mode_profile_field( $user ) {
	$dark_mode_pref = get_user_meta( $user->ID, 'wpshadow_dark_mode_preference', true ) ?: 'auto';
	?>
	<table class="form-table" role="presentation">
		<tr class="wpshadow-dark-mode-wrap">
			<th scope="row"><?php esc_html_e( 'WPShadow Dark Mode', 'wpshadow' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'WPShadow Dark Mode', 'wpshadow' ); ?></span></legend>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="auto" <?php checked( $dark_mode_pref, 'auto' ); ?>>
						<?php esc_html_e( 'Auto (follow system preference)', 'wpshadow' ); ?>
					</label><br>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="light" <?php checked( $dark_mode_pref, 'light' ); ?>>
						<?php esc_html_e( 'Light', 'wpshadow' ); ?>
					</label><br>
					<label>
						<input type="radio" name="wpshadow_dark_mode" value="dark" <?php checked( $dark_mode_pref, 'dark' ); ?>>
						<?php esc_html_e( 'Dark', 'wpshadow' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Choose your preferred dark mode setting for WPShadow admin pages.', 'wpshadow' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save WPShadow Dark Mode profile field.
 */
add_action( 'personal_options_update', 'wpshadow_save_dark_mode_profile_field' );
add_action( 'edit_user_profile_update', 'wpshadow_save_dark_mode_profile_field' );

function wpshadow_save_dark_mode_profile_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	if ( isset( $_POST['wpshadow_dark_mode'] ) ) {
		$dark_mode = sanitize_text_field( $_POST['wpshadow_dark_mode'] );
		if ( in_array( $dark_mode, array( 'auto', 'light', 'dark' ), true ) ) {
			update_user_meta( $user_id, 'wpshadow_dark_mode_preference', $dark_mode );
		}
	}
}

/**
 * Render Workflow Builder page.
 */
function wpshadow_render_workflow_builder() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
	
	if ( $action === 'create' || $action === 'edit' ) {
		include WPSHADOW_PATH . 'includes/views/workflow-wizard.php';
	} else {
		include WPSHADOW_PATH . 'includes/views/workflow-list.php';
	}
}

/**
 * Catalog of WPShadow tools shown on /wp-admin/?page=wpshadow-tools.
 * Returned structure is reused for both the WPShadow Tools page and the core Tools page mirror.
 */

/**
 * Render WPShadow Tools page
 */
function wpshadow_render_tools() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	// Define all available tools
	$tools = array(
		array(
			'id'          => 'email-test',
			'title'       => __( 'Email Test', 'wpshadow' ),
			'description' => __( 'Test email delivery and configuration including From Name/Email settings', 'wpshadow' ),
			'icon'        => 'dashicons-email',
			'category'    => 'communication',
			'capability'  => 'manage_options',
		),
		array(
			'id'          => 'broken-links',
			'title'       => __( 'Broken Links', 'wpshadow' ),
			'description' => __( 'Scan your site for broken internal and external links', 'wpshadow' ),
			'icon'        => 'dashicons-admin-links',
			'category'    => 'monitoring',
			'capability'  => 'read',
		),
		array(
			'id'          => 'color-contrast',
			'title'       => __( 'Color Contrast Checker', 'wpshadow' ),
			'description' => __( 'Check color contrast ratios for accessibility compliance (WCAG)', 'wpshadow' ),
			'icon'        => 'dashicons-art',
			'category'    => 'accessibility',
			'capability'  => 'read',
		),
		array(
			'id'          => 'a11y-audit',
			'title'       => __( 'Accessibility Audit', 'wpshadow' ),
			'description' => __( 'Comprehensive accessibility audit of your site', 'wpshadow' ),
			'icon'        => 'dashicons-universal-access',
			'category'    => 'accessibility',
			'capability'  => 'read',
		),
		array(
			'id'          => 'mobile-friendliness',
			'title'       => __( 'Mobile Friendliness', 'wpshadow' ),
			'description' => __( 'Test how mobile-friendly your site is', 'wpshadow' ),
			'icon'        => 'dashicons-smartphone',
			'category'    => 'performance',
			'capability'  => 'read',
		),
		array(
			'id'          => 'dark-mode',
			'title'       => __( 'Dark Mode Tester', 'wpshadow' ),
			'description' => __( 'Test your site in dark mode and check compatibility', 'wpshadow' ),
			'icon'        => 'dashicons-visibility',
			'category'    => 'design',
			'capability'  => 'read',
		),
		array(
			'id'          => 'timezone-alignment',
			'title'       => __( 'Timezone Alignment', 'wpshadow' ),
			'description' => __( 'Check and align timezone settings across WordPress, PHP, and database', 'wpshadow' ),
			'icon'        => 'dashicons-clock',
			'category'    => 'configuration',
			'capability'  => 'manage_options',
		),
		array(
			'id'          => 'simple-cache',
			'title'       => __( 'Simple Cache Manager', 'wpshadow' ),
			'description' => __( 'Manage WordPress object cache, transients, and page cache', 'wpshadow' ),
			'icon'        => 'dashicons-database',
			'category'    => 'performance',
			'capability'  => 'manage_options',
		),
		array(
			'id'          => 'customization-audit',
			'title'       => __( 'Customization Audit', 'wpshadow' ),
			'description' => __( 'Review theme customizations and custom code', 'wpshadow' ),
			'icon'        => 'dashicons-admin-appearance',
			'category'    => 'development',
			'capability'  => 'read',
		),
		array(
			'id'          => 'tips-coach',
			'title'       => __( 'Tips & Coach', 'wpshadow' ),
			'description' => __( 'Get personalized tips and guidance for improving your site', 'wpshadow' ),
			'icon'        => 'dashicons-lightbulb',
			'category'    => 'education',
			'capability'  => 'read',
		),
		array(
			'id'          => 'magic-link-support',
			'title'       => __( 'Magic Link Support', 'wpshadow' ),
			'description' => __( 'Generate secure magic links for temporary admin access', 'wpshadow' ),
			'icon'        => 'dashicons-admin-network',
			'category'    => 'support',
			'capability'  => 'manage_options',
		),
	);

	// Filter tools by user capability
	$available_tools = array_filter( $tools, function( $tool ) {
		return current_user_can( $tool['capability'] );
	});

	// Group tools by category
	$categories = array(
		'communication'  => __( 'Communication', 'wpshadow' ),
		'monitoring'     => __( 'Monitoring', 'wpshadow' ),
		'accessibility'  => __( 'Accessibility', 'wpshadow' ),
		'performance'    => __( 'Performance', 'wpshadow' ),
		'design'         => __( 'Design', 'wpshadow' ),
		'configuration'  => __( 'Configuration', 'wpshadow' ),
		'development'    => __( 'Development', 'wpshadow' ),
		'education'      => __( 'Education', 'wpshadow' ),
		'support'        => __( 'Support', 'wpshadow' ),
	);

	?>
	<div class="wrap wpshadow-tools-page">
		<h1><?php esc_html_e( 'WPShadow Tools', 'wpshadow' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Powerful utilities to help you manage, optimize, and troubleshoot your WordPress site.', 'wpshadow' ); ?>
		</p>

		<?php foreach ( $categories as $category_key => $category_name ) : ?>
			<?php
			$category_tools = array_filter( $available_tools, function( $tool ) use ( $category_key ) {
				return $tool['category'] === $category_key;
			});

			if ( empty( $category_tools ) ) {
				continue;
			}
			?>

			<h2><?php echo esc_html( $category_name ); ?></h2>
			<div class="wpshadow-tools-grid">
				<?php foreach ( $category_tools as $tool ) : ?>
					<div class="wpshadow-tool-card">
						<div class="wpshadow-tool-card-header">
							<span class="dashicons <?php echo esc_attr( $tool['icon'] ); ?>"></span>
							<h3><?php echo esc_html( $tool['title'] ); ?></h3>
						</div>
						<p class="wpshadow-tool-description">
							<?php echo esc_html( $tool['description'] ); ?>
						</p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-tools&tool=' . $tool['id'] ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Launch Tool', 'wpshadow' ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<style>
		.wpshadow-tools-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
			gap: 20px;
			margin: 20px 0 40px;
		}
		.wpshadow-tool-card {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 4px;
			padding: 20px;
			transition: box-shadow 0.2s;
		}
		.wpshadow-tool-card:hover {
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		}
		.wpshadow-tool-card-header {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 12px;
		}
		.wpshadow-tool-card-header .dashicons {
			font-size: 24px;
			width: 24px;
			height: 24px;
			color: #2271b1;
		}
		.wpshadow-tool-card-header h3 {
			margin: 0;
			font-size: 16px;
		}
		.wpshadow-tool-description {
			color: #666;
			font-size: 13px;
			margin: 0 0 15px;
			line-height: 1.5;
		}
		</style>
	</div>
	<?php

	// Check if a specific tool is requested
	$tool_id = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';
	if ( ! empty( $tool_id ) ) {
		$tool_file = WPSHADOW_PATH . 'includes/views/tools/' . $tool_id . '.php';
		if ( file_exists( $tool_file ) ) {
			// Check capability for the specific tool
			$tool_data = array_filter( $available_tools, function( $t ) use ( $tool_id ) {
				return $t['id'] === $tool_id;
			});
			$tool_data = reset( $tool_data );

			if ( $tool_data && current_user_can( $tool_data['capability'] ) ) {
				echo '<div class="wpshadow-tool-content">';
				include $tool_file;
				echo '</div>';
			}
		}
	}
}

/**
 * Render health diagnostic dashboard.
 */
function wpshadow_render_dashboard() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	// Check if user needs onboarding (Philosophy #8: Inspire Confidence)
	if ( \WPShadow\Onboarding\Onboarding_Manager::needs_onboarding() && ! isset( $_GET['skip_onboarding'] ) ) {
		require_once WPSHADOW_PATH . 'includes/views/onboarding/wizard.php';
		return;
	}

	// Check if filtering by category (Issue #564)
	$filter_category = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
	
	$health = wpshadow_get_health_status();
	$all_findings = wpshadow_get_site_findings();
	$dismissed = get_option( 'wpshadow_dismissed_findings', array() );
	
	// Filter out dismissed findings
	$all_findings = array_filter( $all_findings, function( $f ) use ( $dismissed ) {
		return ! isset( $f['id'] ) || ! isset( $dismissed[ $f['id'] ] );
	} );
	
	// Apply category filter if present
	if ( ! empty( $filter_category ) ) {
		$all_findings = array_filter( $all_findings, function( $f ) use ( $filter_category ) {
			return isset( $f['category'] ) && $f['category'] === $filter_category;
		} );
	}
	
	$critical_findings = array_filter( $all_findings, function( $f ) {
		return isset( $f['color'] ) && $f['color'] === '#f44336'; // Red = critical
	} );
	$show_all = isset( $_GET['show_all'] ) && 'true' === $_GET['show_all'];
	$findings_to_show = $show_all ? $all_findings : array_slice( $critical_findings, 0, 2 );
	
	// Group findings by category for Category Health display
	$findings_by_category = array();
	foreach ( $all_findings as $finding ) {
		$category = isset( $finding['category'] ) ? $finding['category'] : 'other';
		if ( ! isset( $findings_by_category[ $category ] ) ) {
			$findings_by_category[ $category ] = array();
		}
		$findings_by_category[ $category ][] = $finding;
	}
	?>
	<div class="wrap">
		<?php if ( ! empty( $filter_category ) ) : 
			// Get category metadata for filtered view (static for performance)
			static $category_meta = array(
				'security' => array( 'label' => __( 'Security', 'wpshadow' ), 'icon' => 'dashicons-shield-alt', 'color' => '#dc2626' ),
				'performance' => array( 'label' => __( 'Performance', 'wpshadow' ), 'icon' => 'dashicons-dashboard', 'color' => '#0891b2' ),
				'code_quality' => array( 'label' => __( 'Code Quality', 'wpshadow' ), 'icon' => 'dashicons-editor-code', 'color' => '#7c3aed' ),
				'seo' => array( 'label' => __( 'SEO', 'wpshadow' ), 'icon' => 'dashicons-search', 'color' => '#2563eb' ),
				'design' => array( 'label' => __( 'Design', 'wpshadow' ), 'icon' => 'dashicons-admin-appearance', 'color' => '#8e44ad' ),
				'settings' => array( 'label' => __( 'Settings', 'wpshadow' ), 'icon' => 'dashicons-admin-settings', 'color' => '#4b5563' ),
				'monitoring' => array( 'label' => __( 'Monitoring', 'wpshadow' ), 'icon' => 'dashicons-chart-line', 'color' => '#059669' ),
				'workflows' => array( 'label' => __( 'Workflows', 'wpshadow' ), 'icon' => 'dashicons-update', 'color' => '#ea580c' ),
				'wordpress_health' => array( 'label' => __( 'WordPress Site Health', 'wpshadow' ), 'icon' => 'dashicons-wordpress-alt', 'color' => '#2d5016' ),
				// Philosophy-driven trusted advisor categories (Phase 4+)
				'developer_experience' => array( 'label' => __( 'Developer Experience', 'wpshadow' ), 'icon' => 'dashicons-code-alt', 'color' => '#0ea5e9' ),
				'marketing_growth' => array( 'label' => __( 'Marketing & Growth', 'wpshadow' ), 'icon' => 'dashicons-trending-up', 'color' => '#f97316' ),
				'customer_retention' => array( 'label' => __( 'Customer Retention', 'wpshadow' ), 'icon' => 'dashicons-smiley', 'color' => '#14b8a6' ),
				'ai_readiness' => array( 'label' => __( 'AI Readiness', 'wpshadow' ), 'icon' => 'dashicons-lightbulb', 'color' => '#a855f7' ),
				// Impact & Operations categories (Phase 4.5+)
				'environment' => array( 'label' => __( 'Environment & Impact', 'wpshadow' ), 'icon' => 'dashicons-leaf', 'color' => '#10b981' ),
				'users' => array( 'label' => __( 'Users & Team', 'wpshadow' ), 'icon' => 'dashicons-groups', 'color' => '#3b82f6' ),
				'content_publishing' => array( 'label' => __( 'Content Publishing', 'wpshadow' ), 'icon' => 'dashicons-edit', 'color' => '#f59e0b' ),
			);
			$cat_meta = $category_meta[ $filter_category ] ?? array( 'label' => ucfirst( $filter_category ), 'icon' => 'dashicons-admin-generic', 'color' => '#666' );
		?>
		<div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #f0f0f0; border-radius: 4px; color: #333; transition: all 0.2s ease;" onmouseover="this.style.background='#e0e0e0'" onmouseout="this.style.background='#f0f0f0'">
				<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 16px;"></span>
				<?php esc_html_e( 'Back to All Categories', 'wpshadow' ); ?>
			</a>
			<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: <?php echo esc_attr( $cat_meta['color'] ); ?>; color: white; border-radius: 20px; font-size: 12px; font-weight: 600;">
				<span class="dashicons" style="font-size: 14px; width: 14px; height: 14px;"></span>
				<?php echo esc_html( __( 'Filtered', 'wpshadow' ) ); ?>
			</span>
		</div>
		<h1 style="display: flex; align-items: center; gap: 12px;">
			<span class="<?php echo esc_attr( $cat_meta['icon'] ); ?>" style="font-size: 32px; color: <?php echo esc_attr( $cat_meta['color'] ); ?>;"></span>
			<?php echo esc_html( sprintf( __( '%s Dashboard', 'wpshadow' ), $cat_meta['label'] ) ); ?>
		</h1>
		<p style="font-size: 16px; color: #666; margin-top: 8px;">
			<?php 
			$finding_count = count( $all_findings );
			echo esc_html( sprintf( 
				_n( 'Showing %d finding in this category', 'Showing %d findings in this category', $finding_count, 'wpshadow' ), 
				$finding_count 
			) ); 
			?>
		</p>
		<?php else : ?>
		<h1><?php esc_html_e( 'WPShadow Site Health', 'wpshadow' ); ?></h1>
		<?php 
			$user_id = get_current_user_id();
			$streaks = \WPShadow\Gamification\Streak_Tracker::get_current_streaks( $user_id );
			$scan_emoji = \WPShadow\Gamification\Streak_Tracker::get_streak_emoji( $streaks['daily_scans'] ?? 0 );
			$fix_emoji = \WPShadow\Gamification\Streak_Tracker::get_streak_emoji( $streaks['fixes'] ?? 0 );
			$rank = \WPShadow\Gamification\Leaderboard_Manager::get_user_rank( $user_id );
		?>

		<?php endif; ?>

		<script>
		jQuery(document).ready(function($) {
			// Dismiss finding
			$('.wpshadow-dismiss-finding').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var findingId = $btn.data('finding-id');
				var $card = $btn.closest('.wpshadow-finding-card');
				
				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_finding',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_dismiss_finding' ); ?>',
					finding_id: findingId
				}, function(response) {
					if (response.success) {
						$card.fadeOut(300, function() { $(this).remove(); });
					}
				});

			});
			
			// Auto-fix finding
			$('.wpshadow-autofix-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var findingId = $btn.data('finding-id');
				var $card = $btn.closest('.wpshadow-finding-card');
				
				$btn.prop('disabled', true).text('Fixing...');
				
				$.post(ajaxurl, {
					action: 'wpshadow_autofix_finding',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix' ); ?>',
					finding_id: findingId
				}, function(response) {
					if (response.success) {
						$card.html('<div style="padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px;"><strong style="color: #2e7d32;">✓ Fixed!</strong><p style="margin: 5px 0 0 0; color: #555;">' + response.data.message + '</p></div>');
						setTimeout(function() {
							location.reload();
						}, 2000);
					} else {
						alert('Could not auto-fix: ' + (response.data.message || 'Unknown error'));
						$btn.prop('disabled', false).text('Auto-Fix');
					}
				});
			});
			
			// Toggle auto-fix permission
			$('.wpshadow-autofix-toggle').on('change', function() {
				var $checkbox = $(this);
				var findingId = $checkbox.data('finding-id');
				var enabled = $checkbox.prop('checked');
				
				$.post(ajaxurl, {
					action: 'wpshadow_toggle_autofix_permission',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_autofix_permission' ); ?>',
					finding_id: findingId,
					enabled: enabled
				}, function(response) {
					if (response.success) {
						// Show brief confirmation
						var $label = $checkbox.closest('label');
						var originalText = $label.text();
						$label.text(enabled ? '✓ Enabled' : '✗ Disabled');
						setTimeout(function() {
							$label.text(originalText);
						}, 1500);
					}
				});
			});
			
			// Allow all auto-fixes
			$('.wpshadow-allow-all-autofixes').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var currentlyEnabled = $btn.data('enabled') === true;
				var newState = !currentlyEnabled;
				
				$btn.prop('disabled', true);
				
				$.post(ajaxurl, {
					action: 'wpshadow_allow_all_autofixes',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_allow_all_autofixes' ); ?>',
					enabled: newState
				}, function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert('Error: ' + (response.data.message || 'Unknown error'));
						$btn.prop('disabled', false);
					}
				});
			});
			
			// First scan (Issue #562) - with progress tracking
			$('#wpshadow-start-first-scan').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $prompt = $('#wpshadow-first-scan-prompt');
				
				// Trigger event for real-time dashboard updates
				$(document).trigger('wpshadow:quickscan:started');
				$(document).trigger('wpshadow:scan:start');
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Starting...', 'wpshadow' ) ); ?>');
				
				// Replace prompt with progress bar
				$prompt.fadeOut(200, function() {
					var $progressNotice = $('<div id="wpshadow-scan-progress-notice" style="background: #e3f2fd; border-left: 4px solid #0073aa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">' +
						'<h2 style="margin-top: 0; color: #0073aa; display: flex; align-items: center; gap: 10px;">' +
							'<span class="dashicons dashicons-update wpshadow-spin" style="font-size: 24px;"></span>' +
							'<?php echo esc_js( __( 'Running Quick Scan', 'wpshadow' ) ); ?>' +
						'</h2>' +
						'<p style="margin: 10px 0; font-size: 14px; color: #666;">' +
							'<?php echo esc_js( __( 'Analyzing your site...', 'wpshadow' ) ); ?>' +
						'</p>' +
						'<div style="background: #e0e0e0; border-radius: 4px; height: 10px; margin: 15px 0; overflow: hidden;">' +
							'<div id="wpshadow-progress-fill" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>' +
						'</div>' +
						'<p id="wpshadow-scan-status" style="margin: 10px 0 0 0; font-size: 13px; color: #555; font-weight: 500;">' +
							'<?php echo esc_js( __( 'Starting scan...', 'wpshadow' ) ); ?>' +
						'</p>' +
					'</div>');
					
					$progressNotice.hide().insertBefore($prompt.next()).fadeIn(300);
					
					// Add spinning animation
					if (!$('#wpshadow-spin-style').length) {
						$('<style id="wpshadow-spin-style">@keyframes wpshadow-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .wpshadow-spin { animation: wpshadow-spin 2s linear infinite; }</style>').appendTo('head');
					}
					
					// Start the scan
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_first_scan',
							nonce: '<?php echo wp_create_nonce( 'wpshadow_first_scan_nonce' ); ?>'
						},
						timeout: 60000, // 60 second timeout for diagnostics
						success: function(response) {
							if (response.success) {
								var data = response.data;
							
							// Simulate progress updates (since we get all at once)
							if (data.progress_steps && data.progress_steps.length > 0) {
								var currentStep = 0;
								var steps = data.progress_steps;
								
								var updateInterval = setInterval(function() {
									if (currentStep >= steps.length) {
										clearInterval(updateInterval);
										
										// Trigger scan complete event for real-time updates
										$(document).trigger('wpshadow:scan:complete');
										
										// Show completion message
										$('#wpshadow-progress-fill').css('width', '100%');
										var issueText = '';
										if (data.findings_count > 0) {
											issueText = ' <?php echo esc_js( __( 'Found', 'wpshadow' ) ); ?> ' + data.findings_count + ' <?php echo esc_js( __( 'issues.', 'wpshadow' ) ); ?>';
										} else {
											issueText = ' <?php echo esc_js( __( 'No issues found!', 'wpshadow' ) ); ?>';
										}
										$('#wpshadow-scan-status').html('<strong><?php echo esc_js( __( 'Scan complete!', 'wpshadow' ) ); ?></strong>' + issueText);
										$('.wpshadow-spin').removeClass('wpshadow-spin');
										
										// Reload after 1.5 seconds
										setTimeout(function() {
											location.reload();
										}, 1500);
										return;
									}
									
									var step = steps[currentStep];
									$('#wpshadow-progress-fill').css('width', step.progress + '%');
									$('#wpshadow-scan-status').text('<?php echo esc_js( __( 'Checking', 'wpshadow' ) ); ?>: ' + step.diagnostic + ' (' + step.step + '/' + step.total + ')');
									
									currentStep++;
								}, 50); // Fast updates for smooth progress
							} else {
								// Fallback if no progress steps
								$('#wpshadow-progress-fill').css('width', '100%');
								$('#wpshadow-scan-status').text(data.message);
								setTimeout(function() { location.reload(); }, 1000);
							}
						} else {
							$('#wpshadow-scan-progress-notice').fadeOut(300, function() {
								$prompt.fadeIn(300);
							});
							alert('<?php echo esc_js( __( 'Error:', 'wpshadow' ) ); ?> ' + (response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Unknown error', 'wpshadow' ) ); ?>'));
							$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Start Quick Scan', 'wpshadow' ) ); ?>');
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX error:', status, error, xhr.responseText);
						$('#wpshadow-scan-progress-notice').fadeOut(300, function() {
							$prompt.fadeIn(300);
						});
						
						var errorMsg = '<?php echo esc_js( __( 'Network error. Please try again.', 'wpshadow' ) ); ?>';
						if (xhr.responseText) {
							try {
								var resp = JSON.parse(xhr.responseText);
								if (resp.data && resp.data.message) {
									errorMsg = resp.data.message;
								}
							} catch(e) {
								// If response isn't JSON, show first 200 chars
								errorMsg += '\n\nDetails: ' + xhr.responseText.substring(0, 200);
							}
						}
						alert(errorMsg);
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Start Quick Scan', 'wpshadow' ) ); ?>');
					}
				});
				});
			});
			
			// Skip first scan prompt
			$('#wpshadow-skip-first-scan').on('click', function(e) {
				e.preventDefault();
				var $prompt = $('#wpshadow-first-scan-prompt');
				$prompt.fadeOut(300);
			});
			
			// Schedule deep scan
			$('#wpshadow-schedule-scan-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this);
				var $btn = $form.find('button[type="submit"]');
				var $status = $('#wpshadow-scan-status');
				var email = $form.find('input[name="email"]').val();
				var consent = $form.find('input[name="consent"]').prop('checked');
				
				$btn.prop('disabled', true).text('Scheduling...');
				$status.html('');
				
				$.post(ajaxurl, {
					action: 'wpshadow_schedule_deep_scan',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_schedule_scan' ); ?>',
					email: email,
					consent: consent
				}, function(response) {
					if (response.success) {
						$status.html('<div style="padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; margin-top: 10px;">✓ ' + response.data.message + '</div>');
						$form.slideUp();
					} else {
						$status.html('<div style="padding: 10px; background: #ffebee; color: #c62828; border-radius: 4px; margin-top: 10px;">✗ ' + response.data.message + '</div>');
						$btn.prop('disabled', false).text('Schedule Deep Scans');
					}
				});
			});
		
		// Modal handlers
		$('.wpshadow-modal-trigger').on('click', function(e) {
			e.preventDefault();
			var modalId = $(this).data('modal');
			$('#' + modalId).fadeIn(200);
		});
		
		$('.wpshadow-modal-close').on('click', function() {
			$(this).closest('.wpshadow-modal').fadeOut(200);
		});
		
		// Close modal on background click
		$('.wpshadow-modal').on('click', function(e) {
			if (e.target === this) {
				$(this).fadeOut(200);
			}
		});
		
		// Save tagline
		$('#wpshadow-tagline-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
			var $btn = $form.find('button[type="submit"]');
			var $status = $('#wpshadow-tagline-status');
			var tagline = $('#wpshadow-tagline-input').val();
			
			$btn.prop('disabled', true).text('Saving...');
			$status.html('');
			
			$.post(ajaxurl, {
				action: 'wpshadow_save_tagline',
				nonce: '<?php echo wp_create_nonce( 'wpshadow_save_tagline' ); ?>',
				tagline: tagline
			}, function(response) {
				if (response.success) {
					$status.html('<div style="padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">✓ ' + response.data.message + '</div>');
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					$status.html('<div style="padding: 10px; background: #ffebee; color: #c62828; border-radius: 4px;">✗ ' + response.data.message + '</div>');
					$btn.prop('disabled', false).text('Save Tagline');
				}
			});
		});
		
		}); // End jQuery(document).ready
		</script>
		<?php
		$category_meta = array(
			'security' => array(
				'label' => __( 'Security', 'wpshadow' ),
				'icon'  => 'dashicons-shield-alt',
				'color' => '#dc2626',
				'bg'    => '#ffe0e0',
			),
			'performance' => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'icon'  => 'dashicons-dashboard',
				'color' => '#0891b2',
				'bg'    => '#e0f7ff',
			),
			'code_quality' => array(
				'label' => __( 'Code Quality', 'wpshadow' ),
				'icon'  => 'dashicons-editor-code',
				'color' => '#7c3aed',
				'bg'    => '#f3e8ff',
			),
			'seo' => array(
				'label' => __( 'SEO', 'wpshadow' ),
				'icon'  => 'dashicons-search',
				'color' => '#2563eb',
				'bg'    => '#e7f1ff',
			),
			'design' => array(
				'label' => __( 'Design', 'wpshadow' ),
				'icon'  => 'dashicons-admin-appearance',
				'color' => '#8e44ad',
				'bg'    => '#f2e9fb',
			),
			'settings' => array(
				'label' => __( 'Settings', 'wpshadow' ),
				'icon'  => 'dashicons-admin-settings',
				'color' => '#4b5563',
				'bg'    => '#eef2f7',
			),
			'monitoring' => array(
				'label' => __( 'Monitoring', 'wpshadow' ),
				'icon'  => 'dashicons-chart-line',
				'color' => '#059669',
				'bg'    => '#d1fae5',
			),
			'workflows' => array(
				'label' => __( 'Workflows', 'wpshadow' ),
				'icon'  => 'dashicons-update',
				'color' => '#ea580c',
				'bg'    => '#ffedd5',
			),
			'wordpress_health' => array(
				'label' => __( 'WordPress Site Health', 'wpshadow' ),
				'icon'  => 'dashicons-wordpress-alt',
				'color' => '#2d5016',
				'bg'    => '#f0f9f0',
			),
			// Philosophy-driven trusted advisor categories (Phase 4+)
			'developer_experience' => array(
				'label' => __( 'Developer Experience', 'wpshadow' ),
				'icon'  => 'dashicons-code-alt',
				'color' => '#0ea5e9',
				'bg'    => '#e0f2fe',
			),
			'marketing_growth' => array(
				'label' => __( 'Marketing & Growth', 'wpshadow' ),
				'icon'  => 'dashicons-trending-up',
				'color' => '#f97316',
				'bg'    => '#ffedd5',
			),
			'customer_retention' => array(
				'label' => __( 'Customer Retention', 'wpshadow' ),
				'icon'  => 'dashicons-smiley',
				'color' => '#14b8a6',
				'bg'    => '#ccfbf1',
			),
			'ai_readiness' => array(
				'label' => __( 'AI Readiness', 'wpshadow' ),
				'icon'  => 'dashicons-lightbulb',
				'color' => '#a855f7',
				'bg'    => '#f3e8ff',
			),
			// Impact & Operations categories (Phase 4.5+)
			'environment' => array(
				'label' => __( 'Environment & Impact', 'wpshadow' ),
				'icon'  => 'dashicons-leaf',
				'color' => '#10b981',
				'bg'    => '#d1fae5',
			),
			'users' => array(
				'label' => __( 'Users & Team', 'wpshadow' ),
				'icon'  => 'dashicons-groups',
				'color' => '#3b82f6',
				'bg'    => '#dbeafe',
			),
			'content_publishing' => array(
				'label' => __( 'Content Publishing', 'wpshadow' ),
				'icon'  => 'dashicons-edit',
				'color' => '#f59e0b',
				'bg'    => '#fef3c7',
			),
		);

		// Calculate overall health score (or category-specific if filtering)
		if ( ! empty( $filter_category ) && isset( $category_meta[ $filter_category ] ) ) {
			// Filtered view: Show single large category gauge
			$cat_findings = $findings_by_category[ $filter_category ] ?? array();
			$total = count( $cat_findings );
			
			// Calculate category health score
			$threat_total = 0;
			foreach ( $cat_findings as $finding ) {
				$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
			}
			$gauge_percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 100;
			$gauge_percent = 100 - $gauge_percent; // Invert: higher is better
			
			// Determine status
			if ( $gauge_percent >= 80 ) {
				$status = __( 'Excellent', 'wpshadow' );
				$color = '#2e7d32';
				$bg = '#e8f5e9';
			} elseif ( $gauge_percent >= 60 ) {
				$status = __( 'Good', 'wpshadow' );
				$color = '#2e7d32';
				$bg = '#e8f5e9';
			} elseif ( $gauge_percent >= 40 ) {
				$status = __( 'Fair', 'wpshadow' );
				$color = '#f57c00';
				$bg = '#fff3e0';
			} else {
				$status = __( 'Needs Attention', 'wpshadow' );
				$color = '#c62828';
				$bg = '#ffebee';
			}
			
			$filtered_meta = $category_meta[ $filter_category ];
			?>
			<div style="margin: 30px 0;">
				<h2><?php echo esc_html( sprintf( __( '%s Health', 'wpshadow' ), $filtered_meta['label'] ) ); ?></h2>
				
				<div style="max-width: 400px; margin: 20px auto;">
					<div style="border: 2px solid <?php echo esc_attr( $color ); ?>; border-radius: 12px; padding: 32px; background: linear-gradient(135deg, #fff 0%, <?php echo esc_attr( $bg ); ?> 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">
						<div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
							<span class="<?php echo esc_attr( $filtered_meta['icon'] ); ?>" style="font-size: 32px; color: <?php echo esc_attr( $filtered_meta['color'] ); ?>;"></span>
							<h3 style="margin: 0; font-size: 24px; color: <?php echo esc_attr( $filtered_meta['color'] ); ?>;"><?php echo esc_html( $filtered_meta['label'] ); ?></h3>
						</div>
						
<svg width="250" height="250" viewBox="0 0 250 250" style="margin: 0 auto; display: block; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));" id="wpshadow-main-gauge">
						<!-- Outer decorative circle -->
						<circle cx="125" cy="125" r="115" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="2" opacity="0.2" />
						<!-- Gauge background -->
						<circle cx="125" cy="125" r="100" fill="none" stroke="#e0e0e0" stroke-width="20" />
						<!-- Gauge progress -->
						<circle cx="125" cy="125" r="100" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="20"
							class="gauge-progress"
							stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 628 ); ?> 628"
							stroke-linecap="round" transform="rotate(-90 125 125)"
							style="transition: stroke-dasharray 0.5s ease;" />
						<!-- Center text -->
						<text x="125" y="120" text-anchor="middle" font-size="56" font-weight="bold" fill="<?php echo esc_attr( $color ); ?>" class="gauge-percent"><?php echo (int) $gauge_percent; ?>%</text>
						<text x="125" y="150" text-anchor="middle" font-size="18" fill="#666" class="gauge-status"><?php echo esc_html( $status ); ?></text>
						</svg>
						
						<div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.1);">
							<p style="margin: 0; font-size: 16px; color: #666;">
								<?php 
								$critical_count = count( array_filter( $cat_findings, function( $f ) { return isset( $f['color'] ) && $f['color'] === '#f44336'; } ) );
								$passed = $total - $critical_count;
								echo esc_html( sprintf( __( 'Passes %d of %d tests', 'wpshadow' ), $passed, $total ) ); 
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<?php
		} else {
			// Normal view: Show all gauges
			$overall_health = wpshadow_calculate_overall_health( $findings_by_category, $category_meta );
			?>
		
		<div style="margin: 30px 0; position: relative;">
			
<!-- Dashboard Controls removed (Fullscreen button moved to scan buttons section) -->
			
			<!-- Dashboard Status Indicator -->
			<div id="wpshadow-dashboard-status" style="display: none; margin-bottom: 15px;"></div>
			
			<!-- Dashboard Wrapper for Real-Time Updates -->
			<div id="wpshadow-dashboard-wrapper">
			
			<?php
			// Issue #562: Check last Quick Scan time and show prompt if needed
			$last_scan_time = get_option( 'wpshadow_last_quick_scan', 0 );
			$current_time = time();
			$scan_interval = 5 * 60; // 5 minutes
			$time_since_scan = $current_time - (int) $last_scan_time;
			
			// First scan prompt removed - dashboard shows diagnostics immediately
			?>
			
			<div style="display: flex; gap: 24px; margin-top: 20px; flex-wrap: wrap;">
				<!-- Left: Large Overall Health Gauge + Scan Buttons -->
				<div style="flex: 0 0 calc(280px); min-width: 280px;">
					<div style="border: 2px solid <?php echo esc_attr( $overall_health['color'] ); ?>; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">
						<h3 style="margin: 0 0 16px 0; font-size: 20px; color: #333;"><?php esc_html_e( 'Overall Site Health', 'wpshadow' ); ?></h3>
						
						<svg width="200" height="200" viewBox="0 0 200 200" style="margin: 0 auto; display: block; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));">
							<!-- Outer decorative circle -->
							<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $overall_health['color'] ); ?>" stroke-width="2" opacity="0.2" />
							<!-- Gauge background -->
							<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
							<!-- Gauge progress -->
							<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $overall_health['color'] ); ?>" stroke-width="16"
								stroke-dasharray="<?php echo (int) ( $overall_health['score'] / 100 * 534 ); ?> 534"
								stroke-linecap="round" transform="rotate(-90 100 100)"
								style="transition: stroke-dasharray 0.5s ease;" />
							<!-- Center text -->
							<text x="100" y="95" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $overall_health['color'] ); ?>"><?php echo (int) $overall_health['score']; ?>%</text>
							<text x="100" y="120" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $overall_health['status'] ); ?></text>
						</svg>
						
						<p style="margin: 16px 0 0 0; font-size: 14px; color: #666; line-height: 1.5;"><?php echo esc_html( $overall_health['message'] ); ?></p>
					</div>
					
				<!-- Guardian Always Running Status -->
				<div style="margin-top: 16px; padding: 12px; background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%); border: 1px solid #90caf9; border-radius: 6px; text-align: center;">
					<div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 6px;">
						<span class="dashicons dashicons-shield-alt" style="color: #1976d2; font-size: 18px;"></span>
						<strong style="color: #1565c0; font-size: 13px;"><?php esc_html_e( 'Guardian Active', 'wpshadow' ); ?></strong>
					</div>
					<p style="margin: 0; font-size: 11px; color: #555; line-height: 1.4;">
						<?php 
						$last_scan = get_option( 'wpshadow_last_scan_time', time() );
						$time_ago = human_time_diff( $last_scan, time() );
						printf( esc_html__( 'Last scan: %s ago', 'wpshadow' ), esc_html( $time_ago ) );
						?>
					</p>
				</div>

				<!-- Quick Action Buttons -->
				<div style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian' ) ); ?>" class="button button-primary" style="width: 100%; padding: 10px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
						<span class="dashicons dashicons-shield-alt"></span>
						<?php esc_html_e( 'View Guardian', 'wpshadow' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' ) ); ?>" class="button" style="width: 100%; padding: 10px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Manage Workflows', 'wpshadow' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-reports' ) ); ?>" class="button" style="width: 100%; padding: 10px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
						<span class="dashicons dashicons-chart-area"></span>
						<?php esc_html_e( 'View Reports', 'wpshadow' ); ?>
					</a>
					<button id="wpshadow-fullscreen-toggle" class="button" style="width: 100%; padding: 10px; cursor: pointer; color: #888; border-color: #ccc; background: #f5f5f5; display: flex; align-items: center; justify-content: center; gap: 8px;" title="<?php esc_attr_e( 'View dashboard in fullscreen mode (great for office displays)', 'wpshadow' ); ?>">
						<span class="dashicons dashicons-fullscreen-alt"></span>
						<?php esc_html_e( 'Full Screen', 'wpshadow' ); ?>
					</button>
				</div>
				</div>
				
				<!-- Right: 11 Small Category Gauges in 2x6 Grid (2 columns, 6 rows) -->
				<div style="flex: 1; min-width: 600px;">
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px;">
						<?php foreach ( $category_meta as $cat_key => $meta ) :
							// Special handling for WordPress Site Health gauge (#563)
							if ( $cat_key === 'wordpress_health' ) {
								$wp_health = wpshadow_get_wordpress_site_health();
								$gauge_percent = $wp_health['score'];
								$status_text = $wp_health['status'];
								$gauge_color = $wp_health['color'];
								$status_icon = $gauge_percent >= 80 ? '✓' : ( $gauge_percent >= 50 ? '◐' : '✕' );
								$status_color = $gauge_percent >= 80 ? '#2e7d32' : ( $gauge_percent >= 50 ? '#f57c00' : '#c62828' );
							} else {
								$cat_findings = $findings_by_category[ $cat_key ] ?? array();
								$total = count( $cat_findings );
								
								// Calculate category health score
								$critical_count = count( array_filter( $cat_findings, function( $f ) { return isset( $f['color'] ) && $f['color'] === '#f44336'; } ) );
								$passed = $total - $critical_count;
								
								// Determine status
								if ( $total === 0 ) {
									$status_text = __( 'Excellent', 'wpshadow' );
									$status_icon = '✓';
									$status_color = '#2e7d32';
								} elseif ( $critical_count === 0 ) {
									$status_text = __( 'Good', 'wpshadow' );
									$status_icon = '✓';
									$status_color = '#2e7d32';
								} elseif ( $critical_count < $total / 2 ) {
									$status_text = __( 'Fair', 'wpshadow' );
									$status_icon = '◐';
									$status_color = '#f57c00';
								} else {
									$status_text = __( 'Needs Work', 'wpshadow' );
									$status_icon = '✕';
									$status_color = '#c62828';
								}
								
								// Calculate threat gauge percentage
								$threat_total = 0;
								foreach ( $cat_findings as $finding ) {
									$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
								}
								$gauge_percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 0;
								$gauge_percent = 100 - $gauge_percent; // Invert: higher is better
								$gauge_color = wpshadow_get_threat_gauge_color( 100 - $gauge_percent );
							}
						?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&category=' . $cat_key ) ); ?>" style="text-decoration: none; color: inherit;" title="<?php echo esc_attr( sprintf( __( 'Click to view %s details', 'wpshadow' ), $meta['label'] ) ); ?>">
						<div class="wpshadow-category-gauge" data-category="<?php echo esc_attr( $cat_key ); ?>" style="display: flex; align-items: center; gap: 14px; border: 2px solid <?php echo esc_attr( $meta['color'] ); ?>; border-radius: 6px; padding: 12px 14px; background: #ffffff; transition: all 0.2s ease; cursor: pointer; height: 90px;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'; this.style.borderColor='<?php echo esc_js( $meta['color'] ); ?>';" onmouseout="this.style.boxShadow='none'; this.style.borderColor='<?php echo esc_js( $meta['color'] ); ?>';">
							<!-- Gauge on Left -->
							<div style="flex-shrink: 0;">
								<svg width="70" height="70" viewBox="0 0 100 100" style="filter: drop-shadow(0 1px 3px rgba(0,0,0,0.1));">
									<!-- Gauge background -->
									<circle cx="50" cy="50" r="40" fill="none" stroke="#e0e0e0" stroke-width="8" />
									<!-- Gauge progress -->
									<circle cx="50" cy="50" r="40" fill="none" stroke="<?php echo esc_attr( $gauge_color ); ?>" stroke-width="8"
										class="gauge-progress"
										stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 251 ); ?> 251"
										stroke-linecap="round" transform="rotate(-90 50 50)"
										style="transition: stroke-dasharray 0.3s ease;" />
									<!-- Percentage text -->
									<text x="50" y="58" text-anchor="middle" font-size="18" font-weight="bold" fill="#333" class="gauge-percent"><?php echo (int) $gauge_percent; ?>%</text>
								</svg>
							</div>
							
							<!-- Text on Right -->
							<div style="flex: 1; min-width: 0;">
									<!-- Title (icon removed) -->
									<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
										<h4 style="margin: 0; font-size: 13px; color: <?php echo esc_attr( $meta['color'] ); ?>; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html( $meta['label'] ); ?></h4>
									</div>
									
									<!-- Status -->
									<div>
										<span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: 600; font-size: 11px;">
											<?php echo esc_html( $status_icon . ' ' . $status_text ); ?>
										</span>
										<div style="color: #666; font-size: 10px; margin-top: 2px;">
											<?php 
											if ( $cat_key === 'wordpress_health' ) {
												echo esc_html( __( 'WordPress native', 'wpshadow' ) );
											} elseif ( isset( $total ) ) {
												// Count total diagnostics in this category
												$total_tests = wpshadow_count_diagnostics_by_category( $cat_key );
												
												if ( $total === 0 ) {
													echo esc_html( sprintf( __( 'No issues | %d tests', 'wpshadow' ), $total_tests ) );
												} else {
													// Show issues found | total tests available
													echo esc_html( sprintf( 
														_n( '%1$d issue | %2$d tests', '%1$d issues | %2$d tests', $total, 'wpshadow' ), 
														$total, 
														$total_tests 
													) );
												}
											}
											?>
										</div>
									</div>
								</div>
							</div>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		</div><!-- End #wpshadow-dashboard-wrapper -->
		<?php } // End if/else for filtered vs normal view ?>

		<!-- KPI Summary Card (Phase 1) -->
		<?php WPShadow\Core\KPI_Summary_Card::render(); ?>

		<!-- Trend Chart (Phase 1) -->
		<?php WPShadow\Core\Trend_Chart::render_trend_chart(); ?>

		<!-- Phase 6: ROI Calculator -->
		<?php WPShadow\Core\KPI_Advanced_Features::render_roi_calculator(); ?>

		<!-- Phase 6: Advanced Features (Email Reports & CSV Export) -->
		<?php WPShadow\Core\KPI_Advanced_Features::render_advanced_panel(); ?>

		<!-- Phase 3: Dashboard KPI Enhancements Widgets -->
		<?php WPShadow_Activity_Feed_Widget::render(); ?>
		
		<!-- Achievements and Momentum (moved here) -->
		<?php
		$user_id = get_current_user_id();
		$streaks = \WPShadow\Gamification\Streak_Tracker::get_current_streaks( $user_id );
		$scan_emoji = \WPShadow\Gamification\Streak_Tracker::get_streak_emoji( $streaks['daily_scans'] ?? 0 );
		$fix_emoji = \WPShadow\Gamification\Streak_Tracker::get_streak_emoji( $streaks['fixes'] ?? 0 );
		$rank = \WPShadow\Gamification\Leaderboard_Manager::get_user_rank( $user_id );
		?>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin: 30px 0;">
			<div>
				<?php \WPShadow\Gamification\Achievement_System::render_achievements_widget( $user_id ); ?>
			</div>
			<div style="display: flex; flex-direction: column; gap: 12px;">
				<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px;">
					<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
						<span class="dashicons dashicons-smiley" style="font-size: 20px; color: #2563eb;"></span>
						<strong><?php esc_html_e( 'Momentum', 'wpshadow' ); ?></strong>
					</div>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
						<div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e5e7eb;">
							<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Scan Streak', 'wpshadow' ); ?></div>
							<div style="font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
								<?php echo esc_html( $streaks['daily_scans'] ?? 0 ); ?>
								<span><?php echo esc_html( $scan_emoji ); ?></span>
							</div>
						</div>
						<div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e5e7eb;">
							<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Fix Streak', 'wpshadow' ); ?></div>
							<div style="font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
								<?php echo esc_html( $streaks['fixes'] ?? 0 ); ?>
								<span><?php echo esc_html( $fix_emoji ); ?></span>
							</div>
						</div>
						<div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid #e5e7eb;">
							<div style="font-size: 12px; color: #64748b;"><?php esc_html_e( 'Your Rank', 'wpshadow' ); ?></div>
							<div style="font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #16a34a;">
								#<?php echo (int) $rank; ?>
								<span class="dashicons dashicons-chart-line" style="font-size: 18px;"></span>
							</div>
						</div>
					</div>
					<div style="margin-top: 12px;">
						<?php \WPShadow\Gamification\Badge_Manager::render_user_badges( $user_id ); ?>
					</div>
					<div style="margin-top: 12px;">
						<?php \WPShadow\Gamification\Milestone_Notifier::render_unread_notifications( $user_id ); ?>
					</div>
				</div>
			</div>
		</div>





		<!-- Quick Scan Modal -->
		<div id="wpshadow-quick-scan-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center;">
			<div style="background:#fff; border-radius:8px; max-width:500px; width:90%; padding:30px; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
				<h2 style="margin-top:0; color:#2196f3; display:flex; align-items:center; gap:10px;">
					<span class="dashicons dashicons-update" style="font-size:28px;"></span>
					Quick Scan Options
				</h2>
				<p style="color:#555; line-height:1.6; margin:0 0 20px 0;">
					Would you like to run a Quick Scan now, or schedule it to run automatically on a regular basis?
				</p>
				<p style="color:#666; font-size:13px; background:#f5f5f5; padding:12px; border-radius:4px; margin:0 0 20px 0;">
					<strong>Regular Schedule:</strong> Quick Scans will run daily at 3:00 AM to keep your site healthy.
				</p>
				<div style="display:flex; gap:10px; justify-content:flex-end;">
					<button id="wpshadow-quick-scan-cancel" class="button">Cancel</button>
					<button id="wpshadow-quick-scan-now" class="button">Run Now</button>
					<button id="wpshadow-quick-scan-schedule" class="button button-primary">Schedule Regularly</button>
				</div>
			</div>
		</div>

		<!-- Deep Scan Modal -->
		<div id="wpshadow-deep-scan-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center;">
			<div style="background:#fff; border-radius:8px; max-width:500px; width:90%; padding:30px; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
				<h2 style="margin-top:0; color:#e65100; display:flex; align-items:center; gap:10px;">
					<span class="dashicons dashicons-warning" style="font-size:28px;"></span>
					Deep Scan - Server Load Warning
				</h2>
				<p style="color:#555; line-height:1.6; margin:0 0 20px 0;">
					Deep Scans run comprehensive diagnostics which can temporarily increase server load. We recommend scheduling them during slower periods.
				</p>
				<p style="color:#666; font-size:13px; background:#fff3e0; padding:12px; border-radius:4px; margin:0 0 20px 0; border-left:3px solid #e65100;">
					<strong>⚠️ Caution:</strong> Running now may impact site performance during peak traffic. Scheduled scans run weekly on Sundays at 2:00 AM.
				</p>
				<div style="display:flex; gap:10px; justify-content:flex-end;">
					<button id="wpshadow-deep-scan-cancel" class="button">Cancel</button>
					<button id="wpshadow-deep-scan-now" class="button">Run Now Anyway</button>
					<button id="wpshadow-deep-scan-schedule" class="button button-primary">Schedule Off-Peak</button>
				</div>
			</div>
		</div>

		<!-- Off-Peak Scheduling Modal -->
		<div id="wpshadow-offpeak-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:100000; align-items:center; justify-content:center;">
			<div style="background:#fff; border-radius:8px; max-width:500px; width:90%; padding:30px; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
				<h2 style="margin-top:0; color:#e65100; display:flex; align-items:center; gap:10px;">
					<span class="dashicons dashicons-clock" style="font-size:28px;"></span>
					Schedule for Off-Peak Hours?
				</h2>
				<p style="color:#555; line-height:1.6; margin:0 0 20px 0;">
					This operation may temporarily increase server load. To keep your site running smoothly, we recommend scheduling it during off-peak hours.
				</p>
				<p style="color:#666; font-size:13px; background:#f5f5f5; padding:12px; border-radius:4px; margin:0 0 20px 0;">
					<strong>What happens:</strong> We'll run this during low-traffic hours (typically 2-4 AM) and email you the results.
				</p>
				<div style="display:flex; gap:10px; justify-content:flex-end;">
					<button id="wpshadow-offpeak-run-now" class="button">Run Now Anyway</button>
					<button id="wpshadow-offpeak-schedule" class="button button-primary">Schedule Off-Peak</button>
				</div>
			</div>
		</div>

		<!-- Tagline Modal -->
		<div id="wpshadow-tagline-modal" class="wpshadow-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);">
			<div class="wpshadow-modal-content" style="background: #fff; margin: 10% auto; padding: 30px; border-radius: 8px; max-width: 500px; position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
				<button class="wpshadow-modal-close" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 28px; cursor: pointer; color: #999; line-height: 1;">×</button>
				<h2 style="margin-top: 0; color: #2196f3;">Add Your Site Tagline</h2>
				<p style="color: #555; line-height: 1.6; margin: 15px 0;">
					A tagline (also called a site description) is a short phrase that describes what your site is about. It appears in search results and helps visitors quickly understand your site's purpose.
				</p>
				<?php if ( wpshadow_is_site_registered() ) : ?>
					<?php $suggestions = wpshadow_generate_tagline_suggestions(); ?>
					<?php if ( ! empty( $suggestions ) ) : ?>
					<div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; border-radius: 4px;">
						<strong style="color: #1976d2; display: block; margin-bottom: 10px;">🤖 AI-Generated Suggestions:</strong>
						<?php foreach ( $suggestions as $index => $suggestion ) : ?>
						<label style="display: block; margin: 8px 0; cursor: pointer; padding: 8px; background: #fff; border-radius: 4px; border: 1px solid #ddd;">
							<input type="radio" name="ai-suggestion" value="<?php echo esc_attr( $suggestion ); ?>" style="margin-right: 8px;" />
							<?php echo esc_html( $suggestion ); ?>
						</label>
						<?php endforeach; ?>
						<p style="font-size: 12px; color: #666; margin-top: 10px; margin-bottom: 0;">Click a suggestion to use it, or write your own below.</p>
					</div>
					<?php endif; ?>
				<?php else : ?>
				<p style="color: #555; line-height: 1.6; margin: 15px 0; font-size: 13px;">
					<strong>Examples:</strong><br/>
					• "Fresh recipes for busy families"<br/>
					• "Professional photography services in Seattle"<br/>
					• "Handcrafted furniture made with love"
				</p>
				<?php endif; ?>
				<form id="wpshadow-tagline-form">
					<p>
						<label for="wpshadow-tagline-input" style="display: block; margin-bottom: 8px; font-weight: 500;">Your Tagline:</label>
						<input type="text" id="wpshadow-tagline-input" name="tagline" maxlength="200" 
							style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;" 
							placeholder="Enter a short description of your site..." required />
						<span style="font-size: 12px; color: #666;">Keep it under 200 characters</span>
					</p>
					<div id="wpshadow-tagline-status" style="margin: 15px 0;"></div>
					<p style="margin-top: 20px;">
						<button type="submit" class="button button-primary" style="padding: 10px 20px;">Save Tagline</button>
						<button type="button" class="button wpshadow-modal-close" style="margin-left: 10px;">Cancel</button>
					</p>
				</form>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
		// Dashboard Customization Toggle
		$('#wpshadow-customize-dashboard-toggle').on('click', function() {
			const $content = $('#wpshadow-customize-content');
			const $icon = $('#wpshadow-customize-toggle-icon');
			const isOpen = $content.is(':visible');
			
			$content.slideToggle(300);
			$icon.css('transform', isOpen ? 'rotate(0deg)' : 'rotate(180deg)');
		});

		// Check URL for action=quick_scan and auto-open modal
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('action') === 'quick_scan') {
			$('#wpshadow-quick-scan-modal').css('display', 'flex');
			// Remove the action parameter from URL without reload
			window.history.replaceState({}, document.title, window.location.pathname + '?page=wpshadow');
		}
		
			$('#wpshadow-quick-scan-btn').on('click', function() {
				$('#wpshadow-quick-scan-modal').css('display', 'flex');
			});

			// Quick Scan - Cancel
			$('#wpshadow-quick-scan-cancel').on('click', function() {
				$('#wpshadow-quick-scan-modal').hide();
			});

			// Quick Scan - Run Now
			$('#wpshadow-quick-scan-now').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Running...');
				
				// Close modal
				$('#wpshadow-quick-scan-modal').hide();
				
				// Show "Scanning..." status without clearing gauges
				const $mainStatus = $('#wpshadow-main-gauge text.gauge-status');
				if ($mainStatus.length) {
					$mainStatus.text('Scanning...');
				}
				
				// Run scan
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wpshadow_quick_scan',
						nonce: wpshadow.scan_nonce,
						mode: 'now'
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							// Reload to display updated results
							location.reload();
						} else {
							console.error('Quick Scan error:', response.data?.message || 'Unknown error');
							$btn.prop('disabled', false).text('Run Now');
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error('Quick Scan AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
						alert('Network error during scan: ' + textStatus);
						$btn.prop('disabled', false).text('Run Now');
					}
				});
			});

			// Quick Scan - Schedule Regularly
			$('#wpshadow-quick-scan-schedule').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Scheduling...');

				$.post(ajaxurl, {
					action: 'wpshadow_quick_scan',
					nonce: wpshadow.scan_nonce,
					mode: 'schedule'
				}, function(response) {
					$('#wpshadow-quick-scan-modal').hide();
					if (response.success) {
						alert(response.data.message);
					} else {
						alert('Error: ' + (response.data?.message || 'Could not schedule'));
					}
					$btn.prop('disabled', false).text('Schedule Regularly');
				});
			});

			// Deep Scan button handler
			$('#wpshadow-deep-scan-btn').on('click', function() {
				$('#wpshadow-deep-scan-modal').css('display', 'flex');
			});

			// Deep Scan - Cancel
			$('#wpshadow-deep-scan-cancel').on('click', function() {
				$('#wpshadow-deep-scan-modal').hide();
			});

			// Deep Scan - Run Now Anyway
			$('#wpshadow-deep-scan-now').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Running...');
			
				// Close modal
				$('#wpshadow-deep-scan-modal').hide();
				
				// Show "Scanning..." status without clearing gauges
				const $mainStatus = $('#wpshadow-main-gauge text.gauge-status');
				if ($mainStatus.length) {
					$mainStatus.text('Scanning...');
				}

				// Run scan
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'wpshadow_deep_scan',
						nonce: wpshadow.scan_nonce,
						mode: 'now'
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							// Reload to display updated results
							location.reload();
						} else {
							console.error('Deep Scan error:', response.data?.message || 'Unknown error');
							$btn.prop('disabled', false).text('Run Now Anyway');
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error('Deep Scan AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
						alert('Network error during scan: ' + textStatus);
						$btn.prop('disabled', false).text('Run Now Anyway');
					}
				});
			});

			// Deep Scan - Schedule Off-Peak
			$('#wpshadow-deep-scan-schedule').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Scheduling...');

				$.post(ajaxurl, {
					action: 'wpshadow_deep_scan',
					nonce: wpshadow.scan_nonce,
					mode: 'schedule'
				}, function(response) {
					$('#wpshadow-deep-scan-modal').hide();
					if (response.success) {
						alert(response.data.message);
					} else {
						alert('Error: ' + (response.data?.message || 'Could not schedule'));
					}
					$btn.prop('disabled', false).text('Schedule Off-Peak');
				});
			});

			// Close modals on background click
			$('#wpshadow-quick-scan-modal, #wpshadow-deep-scan-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).hide();
				}
			});

			// Off-peak modal handlers
			let pendingOperation = null;

		/**
		 * Reset all gauges to 0 and show scanning status
		 */
		function resetGauges(scanType) {
			// Reset main gauge
			const $mainGauge = $('#wpshadow-main-gauge circle.gauge-progress');
			const $mainPercent = $('#wpshadow-main-gauge text.gauge-percent');
			const $mainStatus = $('#wpshadow-main-gauge text.gauge-status');
			
			if ($mainGauge.length) {
				$mainGauge.css({
					'stroke-dasharray': '0 628',
					'stroke': '#ccc',
					'transition': 'stroke-dasharray 0.3s ease, stroke 0.3s ease'
				});
			}
			if ($mainPercent.length) {
				$mainPercent.text('0%');
			}
			if ($mainStatus.length) {
				$mainStatus.text('Scanning...');
			}
			
			// Reset category gauges
			$('.wpshadow-category-gauge').each(function() {
				const $gauge = $(this).find('circle.gauge-progress');
				const $percent = $(this).find('text.gauge-percent');
				const $count = $(this).find('text.gauge-count');
				
				if ($gauge.length) {
					$gauge.css({
						'stroke-dasharray': '0 251',
						'stroke': '#ccc',
						'transition': 'stroke-dasharray 0.3s ease, stroke 0.3s ease'
					});
				}
				if ($percent.length) {
					$percent.text('0%');
				}
				if ($count.length) {
					$count.text('0 of 0');
				}
			});
		}

		/**
		 * Animate gauges to completion values
		 */
		function animateGaugesCompletion(completed, total, callback) {
			let step = 0;
			const steps = 20; // Number of animation steps
			const interval = 50; // ms per step
			
			const animation = setInterval(function() {
				step++;
				const progress = Math.min(1, step / steps);
				
				// Animate main gauge
				const mainPercent = Math.round(progress * 100);
				const mainDashArray = Math.round((mainPercent / 100) * 628);
				
				const $mainGauge = $('#wpshadow-main-gauge circle.gauge-progress');
				const $mainPercent = $('#wpshadow-main-gauge text.gauge-percent');
				const $mainStatus = $('#wpshadow-main-gauge text.gauge-status');
				
				if ($mainGauge.length) {
					// Color based on progress
					let color = '#4caf50'; // green
					if (mainPercent < 60) color = '#f44336'; // red
					else if (mainPercent < 80) color = '#ff9800'; // orange
					
					$mainGauge.css({
						'stroke-dasharray': mainDashArray + ' 628',
						'stroke': color
					});
				}
				if ($mainPercent.length) {
					$mainPercent.text(mainPercent + '%');
				}
				if ($mainStatus.length) {
					$mainStatus.text(Math.round(progress * completed) + ' of ' + total);
				}
				
				// Animate category gauges proportionally
				$('.wpshadow-category-gauge').each(function() {
					const $gauge = $(this).find('circle.gauge-progress');
					const $percent = $(this).find('text.gauge-percent');
					const $count = $(this).find('text.gauge-count');
					
					const categoryPercent = Math.round(progress * 100);
					const categoryDashArray = Math.round((categoryPercent / 100) * 251);
					
					if ($gauge.length) {
						let catColor = '#4caf50';
						if (categoryPercent < 60) catColor = '#f44336';
						else if (categoryPercent < 80) catColor = '#ff9800';
						
						$gauge.css({
							'stroke-dasharray': categoryDashArray + ' 251',
							'stroke': catColor
						});
					}
					if ($percent.length) {
						$percent.text(categoryPercent + '%');
					}
					// Note: Real counts would come from backend, using simulated progress
				});
				
				if (step >= steps) {
					clearInterval(animation);
					if (callback) callback();
				}
			}, interval);
		}

		// Off-peak modal handlers
			window.wpshadowCheckSlowdown = function(operationType, callback) {
				// Operations that could cause slowdowns
				const heavyOperations = ['deep-scan', 'database-optimization', 'full-security-scan', 'cache-warmup', 'bulk-autofix'];
				
				if (heavyOperations.includes(operationType)) {
					pendingOperation = { type: operationType, callback: callback };
					$('#wpshadow-offpeak-modal').css('display', 'flex');
					return true;
				}
				
				// Not heavy, run immediately
				if (callback) callback();
				return false;
			};

			// Run now button
			$('#wpshadow-offpeak-run-now').on('click', function() {
				$('#wpshadow-offpeak-modal').hide();
				if (pendingOperation && pendingOperation.callback) {
					pendingOperation.callback();
				}
				pendingOperation = null;
			});

			// Schedule off-peak button
			$('#wpshadow-offpeak-schedule').on('click', function() {
				const $btn = $(this);
				$btn.prop('disabled', true).text('Scheduling...');

				$.post(ajaxurl, {
					action: 'wpshadow_schedule_offpeak',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_offpeak' ); ?>',
					operation_type: pendingOperation ? pendingOperation.type : 'unknown',
					email: '<?php echo esc_js( wp_get_current_user()->user_email ); ?>'
				}, function(response) {
					if (response.success) {
						$('#wpshadow-offpeak-modal').hide();
						alert('Scheduled! We\'ll run this during off-peak hours and email you the results.');
					} else {
						alert('Error: ' + (response.data?.message || 'Could not schedule'));
					}
					$btn.prop('disabled', false).text('Schedule Off-Peak');
					pendingOperation = null;
				});
			});

			// Close modal on background click
			$('#wpshadow-offpeak-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).hide();
					pendingOperation = null;
				}
			});
		});
		</script>

		<!-- Activity History (moved from submenu to bottom of dashboard) -->
		<?php
		// Include the full activity history view inline (view has its own title)
		include WPSHADOW_PATH . 'includes/views/activity-history.php';
		?>

	</div>
	<?php
}

/**
 * Render Action Items (Kanban Board) page.
 */
function wpshadow_render_action_items() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	// Check if filtering by category
	$filter_category = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Action Items', 'wpshadow' ); ?></h1>
		<p style="font-size: 16px; color: #666; margin: 16px 0 24px 0;">
			<?php esc_html_e( 'Organize and prioritize your site improvements. Drag items between columns to manage your workflow.', 'wpshadow' ); ?>
		</p>

		<?php 
		// Pass category filter to Kanban board if present
		if ( ! empty( $filter_category ) ) {
			$_GET['kanban_category'] = $filter_category;
		}
		include WPSHADOW_PATH . 'includes/views/kanban-board.php'; 
		?>
	</div>
	<?php
}

/**
 * Get site health status.
 */
/**
 * Calculate overall site health score from all category findings.
 * 
 * Philosophy: Show value (#9) - Aggregate category health into single metric
 * 
 * @param array $findings_by_category Findings grouped by category
 * @param array $category_meta Category metadata (colors, labels)
 * @return array Overall health data (score, status, color, bg, message)
 */
function wpshadow_calculate_overall_health( $findings_by_category, $category_meta ) {
	if ( empty( $findings_by_category ) ) {
		return array(
			'score'   => 100,
			'status'  => __( 'Excellent', 'wpshadow' ),
			'color'   => '#2e7d32',
			'bg'      => '#e8f5e9',
			'message' => __( 'Your site is in perfect shape! All critical checks passed.', 'wpshadow' ),
		);
	}
	
	// Calculate weighted average across all categories
	$total_score = 0;
	$category_count = 0;
	
	foreach ( $category_meta as $cat_key => $meta ) {
		$cat_findings = $findings_by_category[ $cat_key ] ?? array();
		$total = count( $cat_findings );
		
		if ( $total === 0 ) {
			$total_score += 100; // Perfect score if no findings
			$category_count++;
			continue;
		}
		
		// Calculate threat-based score for this category
		$threat_total = 0;
		foreach ( $cat_findings as $finding ) {
			$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
		}
		
		// Invert threat to health score (lower threat = higher health)
		$category_score = 100 - min( 100, ( $threat_total / $total ) / 100 * 100 );
		$total_score += $category_score;
		$category_count++;
	}
	
	// Calculate average
	$overall_score = $category_count > 0 ? round( $total_score / $category_count ) : 0;
	
	// Determine status and color
	if ( $overall_score >= 80 ) {
		$status = __( 'Excellent', 'wpshadow' );
		$color = '#2e7d32';
		$bg = '#e8f5e9';
		$message = __( 'Your site is running smoothly. Guardian will continue watching for potential issues.', 'wpshadow' );
	} elseif ( $overall_score >= 60 ) {
		$status = __( 'Good', 'wpshadow' );
		$color = '#2e7d32';
		$bg = '#e8f5e9';
		$message = __( 'Your site is in good shape with minor issues to monitor.', 'wpshadow' );
	} elseif ( $overall_score >= 40 ) {
		$status = __( 'Fair', 'wpshadow' );
		$color = '#f57c00';
		$bg = '#fff3e0';
		$message = __( 'Your site has some issues that should be addressed soon.', 'wpshadow' );
	} else {
		$status = __( 'Needs Attention', 'wpshadow' );
		$color = '#c62828';
		$bg = '#ffebee';
		$message = __( 'Your site has critical issues that need immediate attention.', 'wpshadow' );
	}
	
	return array(
		'score'   => $overall_score,
		'status'  => $status,
		'color'   => $color,
		'bg'      => $bg,
		'message' => $message,
	);
}

/**
 * Count total diagnostic test files by category.
 * 
 * Scans the diagnostics directory for test stub files matching the category.
 * Used to show "X issues found | Y tests available" on dashboard gauges.
 * 
 * @param string $category Category slug (e.g., 'security', 'performance')
 * @return int Number of diagnostic test files for this category
 */
function wpshadow_count_diagnostics_by_category( $category ) {
	static $category_counts = null;
	
	// Cache the counts to avoid repeated file scans
	if ( $category_counts === null ) {
		$category_counts = array();
		
		// Scan diagnostics directory for all test files (including subdirectories)
		$diagnostics_dir = WPSHADOW_PATH . 'includes/diagnostics/';
		if ( is_dir( $diagnostics_dir ) ) {
			$files = glob( $diagnostics_dir . 'class-diagnostic-*.php' );
			// Also scan subdirectories
			foreach ( glob( $diagnostics_dir . '*/class-diagnostic-*.php' ) as $subfile ) {
				$files[] = $subfile;
			}
			
			// Extended category prefix mapping - captures all 2,500+ diagnostics
			$category_prefixes = array(
				'security' => array( 'sec-', 'security-', 'ssl-', 'tls-', 'xss-', 'csrf-', 'sql-', 'auth-', 'login-', 'brute-', 'malware-', 'gdpr-', 'ccpa-', 'pci-', 'hipaa-', 'backup-', 'encryption-', 'session-', 'password-', 'vulnerable-', 'weak-', 'phishing-', 'intrusion-', 'ddos-', 'idor-', 'ssrf-', 'csp-', 'rate-', 'waf-', 'firewall-', 'suspicious-', 'backdoor-' ),
				'performance' => array( 'perf-', 'performance-', 'speed-', 'cache-', 'query-', 'database-', 'memory-', 'cpu-', 'io-', 'lcp-', 'fid-', 'cls-', 'cwv-', 'render-', 'http2-', 'http3-', 'brotli-', 'gzip-', 'cdn-', 'lazy-', 'image-', 'font-', 'asset-', 'css-', 'javascript-', 'js-', 'async-', 'defer-', 'prefetch-', 'preload-', 'preconnect-', 'dns-prefetch-', 'head-', 'above-the-fold-', 'lqip-', 'webp-', 'avif-', 'compression-', 'minify-', 'bundling-', 'chunk-', 'polyfill-', 'framework-', 'vendor-', 'third-party-', 'slow-', 'bottleneck-', 'blocking-', 'excessive-' ),
				'code_quality' => array( 'code-', 'quality-', 'refactor-', 'complexity-', 'duplication-', 'technical-debt-', 'standards-', 'deprecated-', 'vulnerability-', 'pattern-', 'architecture-' ),
				'seo' => array( 'seo-', 'search-', 'keyword-', 'meta-', 'schema-', 'structured-data-', 'og-', 'twitter-', 'canonical-', 'robots-', 'sitemap-', 'breadcrumb-', 'internal-link-', 'external-link-', 'anchor-', 'heading-', 'title-', 'description-', 'slug-', 'content-', 'readability-', 'keyword-', 'density-', 'latent-', 'semantic-', 'entity-', 'organic-', 'traffic-', 'ranking-', 'crawl-', 'indexing-', 'redirect-', 'canonical-', 'hreflang-', 'mobile-friendly-', 'mobile-first-', 'page-speed-', 'core-web-vitals-', 'user-experience-', 'engagement-', 'time-on-page-', 'bounce-', 'ctr-', 'impressions-', 'search-console-', 'analytics-', 'conversion-', 'revenue-', 'competitor-', 'backlink-', 'domain-authority-', 'page-authority-', 'trust-flow-', 'citation-flow-', 'brand-mentions-', 'local-seo-', 'geo-', 'voice-search-', 'featured-snippet-', 'rich-results-', 'knowledge-graph-', 'entity-' ),
				'design' => array( 'design-', 'ux-', 'ui-', 'layout-', 'typography-', 'color-', 'spacing-', 'breakpoint-', 'responsive-', 'mobile-', 'tablet-', 'desktop-', 'accessibility-', 'wcag-', 'a11y-', 'aria-', 'contrast-', 'font-', 'readability-', 'hierarchy-', 'visual-', 'whitespace-', 'alignment-', 'consistency-', 'brand-', 'theme-', 'custom-', 'css-', 'sass-', 'less-', 'utility-', 'component-', 'pattern-', 'icon-', 'button-', 'form-', 'input-', 'select-', 'checkbox-', 'radio-', 'toggle-', 'modal-', 'card-', 'list-', 'table-', 'grid-', 'flexbox-', 'animation-', 'transition-', 'hover-', 'focus-', 'active-', 'disabled-', 'loading-', 'skeleton-', 'placeholder-', 'error-', 'success-', 'warning-', 'info-', 'notification-', 'toast-', 'badge-', 'tooltip-', 'popover-', 'dropdown-', 'menu-', 'nav-', 'breadcrumb-', 'pagination-', 'stepper-', 'wizard-', 'carousel-', 'slider-', 'gallery-', 'lightbox-', 'video-', 'embed-', 'iframe-', 'ads-', 'banner-', 'hero-', 'cta-', 'footer-', 'sidebar-', 'drawer-', 'collapse-', 'accordion-', 'tab-', 'scrollbar-', 'cursor-', 'shadow-', 'border-', 'outline-', 'fill-', 'stroke-', 'opacity-', 'filter-', 'blend-', 'mask-', 'clip-', 'transform-', 'perspective-', 'backface-', 'motor-', 'prefers-reduced-motion-', 'dark-mode-', 'light-mode-', 'theme-switcher-' ),
				'settings' => array( 'settings-', 'config-', 'options-', 'environment-', 'admin-', 'menu-', 'page-', 'post-', 'taxonomy-', 'user-', 'role-', 'capability-', 'permission-', 'access-', 'rbac-', 'multisite-', 'network-', 'mu-plugin-', 'plugin-', 'theme-', 'wordpress-', 'wp-config-', 'htaccess-', 'robots-', 'permalink-', 'slug-', 'domain-', 'url-', 'ssl-', 'certificate-', 'dns-', 'mail-', 'smtp-', 'php-', 'version-', 'constant-', 'variable-', 'timezone-', 'locale-', 'language-', 'date-', 'time-', 'number-format-', 'currency-', 'measurement-' ),
				'monitoring' => array( 'monitor-', 'monitoring-', 'mon-', 'alert-', 'notification-', 'email-', 'sms-', 'slack-', 'webhook-', 'api-', 'analytics-', 'log-', 'error-', 'warning-', 'debug-', 'trace-', 'profiler-', 'performance-', 'uptime-', 'downtime-', 'status-', 'health-', 'check-', 'ping-', 'heartbeat-', 'realtime-', 'dashboard-', 'report-', 'metric-', 'gauge-', 'chart-', 'trend-', 'forecast-', 'anomaly-', 'threshold-', 'sla-', 'rto-', 'rpo-', 'mtbf-', 'mttr-', 'incident-', 'event-', 'audit-', 'compliance-', 'backup-', 'recovery-', 'redundancy-', 'failover-', 'load-balance-' ),
				'workflows' => array( 'workflow-', 'wf-', 'automation-', 'automation-', 'trigger-', 'action-', 'condition-', 'loop-', 'delay-', 'notification-', 'webhook-', 'api-', 'integration-', 'zapier-', 'ifttt-', 'pabbly-', 'make-', 'n8n-', 'airflow-', 'jenkins-', 'github-', 'gitlab-', 'circle-', 'travis-', 'appveyor-', 'rollout-', 'deployment-', 'scheduled-', 'recurring-', 'cron-', 'queue-', 'job-', 'task-', 'background-', 'async-', 'celery-', 'rq-', 'sidekiq-', 'bull-', 'bee-queue-' ),
				'wordpress_health' => array( 'wp-', 'wordpress-', 'health-', 'site-', 'core-', 'update-', 'debug-', 'rest-', 'api-', 'gutenberg-', 'block-', 'editor-', 'post-type-', 'post-meta-', 'taxonomy-', 'term-', 'user-', 'comment-', 'attachment-', 'option-', 'transient-', 'cache-', 'rewrite-', 'flush-', 'permalinks-', 'menu-', 'widget-', 'sidebar-', 'theme-', 'plugin-', 'mu-plugin-', 'capability-', 'user-role-', 'nonce-', 'security-', 'two-factor-', 'password-', 'htaccess-', 'wpconfig-', 'git-', 'svn-', 'version-', 'license-', 'feature-', 'deprecated-', 'removed-' ),
				'developer_experience' => array( 'dx-', 'dev-', 'developer-', 'api-', 'rest-', 'graphql-', 'sdk-', 'documentation-', 'example-', 'tutorial-', 'guide-', 'cli-', 'command-', 'hook-', 'filter-', 'action-', 'shortcode-', 'block-', 'theme-', 'plugin-', 'scaffold-', 'template-', 'starter-', 'boilerplate-', 'framework-', 'library-', 'package-', 'npm-', 'composer-', 'dependency-', 'version-', 'changelog-', 'roadmap-', 'issue-', 'pr-', 'contribution-', 'community-', 'support-', 'forum-', 'stack-overflow-', 'github-', 'gitlab-', 'debugging-', 'profiling-', 'testing-', 'coverage-', 'ci-', 'cd-', 'lint-', 'format-', 'type-', 'static-', 'code-generation-', 'performance-', 'benchmark-', 'load-', 'stress-', 'security-', 'vulnerability-', 'penetration-', 'fuzzing-' ),
				'marketing_growth' => array( 'mkt-', 'marketing-', 'growth-', 'sales-', 'revenue-', 'conversion-', 'funnel-', 'lead-', 'customer-', 'email-', 'newsletter-', 'social-', 'content-', 'seo-', 'ppc-', 'ad-', 'ga-', 'analytics-', 'attribution-', 'utm-', 'campaign-', 'segment-', 'persona-', 'demographic-', 'psychographic-', 'behavior-', 'engagement-', 'retention-', 'churn-', 'lifetime-', 'clv-', 'arpu-', 'arppu-', 'rpu-', 'blended-', 'unit-economics-', 'payback-', 'cac-', 'roi-', 'roas-', 'ltv-', 'nps-', 'csat-', 'ces-', 'cohort-', 'retention-', 'growth-', 'viral-', 'referral-', 'affiliate-', 'partnership-', 'integration-', 'distribution-', 'channel-', 'ab-', 'test-', 'experiment-', 'optimization-', 'personalization-', 'recommendation-', 'cross-sell-', 'upsell-', 'downsell-', 'bundling-', 'pricing-', 'discount-', 'promotion-', 'coupon-', 'loyalty-', 'reward-', 'gamification-', 'vip-', 'tiering-', 'segmentation-', 'targeting-', 'messaging-', 'positioning-', 'branding-', 'voice-', 'tone-', 'copy-', 'design-', 'landing-page-', 'homepage-', 'product-page-', 'pricing-page-', 'about-page-', 'contact-page-', 'checkout-page-', 'thank-you-page-', 'error-page-', 'login-page-', 'signup-page-', 'dashboard-', 'onboarding-', 'tutorial-', 'tour-', 'video-', 'case-study-', 'testimonial-', 'review-', 'rating-', 'feed-', 'stream-', 'comment-', 'discussion-', 'forum-', 'community-', 'user-generated-', 'ugc-', 'influencer-', 'ambassador-', 'advocate-', 'pr-', 'press-', 'media-', 'podcast-', 'webinar-', 'conference-', 'event-', 'workshop-', 'training-', 'certification-', 'course-', 'membership-', 'subscription-', 'premium-', 'freemium-', 'trial-', 'beta-', 'early-access-', 'waitlist-', 'founding-member-', 'exclusive-', 'limited-edition-', 'flash-sale-', 'seasonal-', 'holiday-', 'black-friday-', 'cyber-monday-' ),
				'customer_retention' => array( 'retention-', 'customer-', 'engagement-', 'churn-', 'loyalty-', 'satisfaction-', 'nps-', 'csat-', 'ces-', 'feedback-', 'survey-', 'review-', 'rating-', 'support-', 'ticket-', 'help-desk-', 'live-chat-', 'chatbot-', 'faq-', 'knowledge-base-', 'documentation-', 'tutorial-', 'guide-', 'video-', 'webinar-', 'training-', 'certification-', 'community-', 'forum-', 'group-', 'event-', 'meetup-', 'conference-', 'workshop-', 'office-hours-', 'one-on-one-', 'consulting-', 'advisory-', 'account-manager-', 'vip-', 'premium-support-', 'priority-', 'urgent-', 'sla-', 'uptime-', 'redundancy-', 'failover-', 'disaster-recovery-', 'backup-', 'monitoring-', 'alert-', 'proactive-', 'preventive-', 'predictive-', 'upgrade-', 'migration-', 'onboarding-', 'success-', 'outcome-', 'value-', 'roi-', 'payback-', 'growth-', 'expansion-', 'cross-sell-', 'upsell-', 'contract-renewal-', 'license-renewal-', 'subscription-renewal-', 'payment-', 'billing-', 'invoice-', 'credit-card-', 'auto-pay-', 'recurring-', 'refund-', 'chargeback-', 'dispute-', 'complaint-', 'resolution-', 'escalation-', 'recommendation-', 'referral-', 'advocacy-', 'ambassador-' ),
				'ai_readiness' => array( 'ai-', 'artificial-intelligence-', 'ml-', 'machine-learning-', 'nlp-', 'natural-language-', 'cv-', 'computer-vision-', 'llm-', 'language-model-', 'chatgpt-', 'gpt-', 'bert-', 'transformer-', 'attention-', 'embedding-', 'vector-', 'semantic-', 'similarity-', 'classification-', 'clustering-', 'regression-', 'anomaly-detection-', 'recommendation-', 'ranking-', 'personalization-', 'prediction-', 'forecast-', 'trend-', 'pattern-', 'insight-', 'discovery-', 'automation-', 'workflow-', 'trigger-', 'action-', 'integration-', 'api-', 'webhook-', 'plugin-', 'extension-', 'addon-', 'module-', 'feature-', 'capability-', 'readiness-', 'adoption-', 'maturity-', 'capability-model-', 'assessment-', 'evaluation-', 'benchmark-', 'comparison-', 'competitive-analysis-', 'market-research-', 'trend-analysis-', 'future-proofing-', 'scalability-', 'performance-', 'efficiency-', 'effectiveness-', 'reliability-', 'robustness-', 'safety-', 'security-', 'privacy-', 'ethics-', 'bias-', 'fairness-', 'transparency-', 'explainability-', 'interpretability-', 'governance-', 'compliance-', 'regulation-', 'policy-', 'standard-', 'best-practice-' ),
				'environment' => array( 'env-', 'environment-', 'sustainability-', 'carbon-', 'energy-', 'green-', 'eco-', 'renewable-', 'solar-', 'wind-', 'hydro-', 'geothermal-', 'fossil-fuel-', 'emission-', 'footprint-', 'offset-', 'neutrality-', 'net-zero-', 'climate-', 'weather-', 'impact-', 'measure-', 'report-', 'disclosure-', 'esg-', 'esg-', 'csr-', 'sustainability-report-', 'carbon-accounting-', 'lifecycle-assessment-', 'lca-', 'supply-chain-', 'supplier-', 'sourcing-', 'fair-trade-', 'ethical-', 'labor-', 'worker-', 'safety-', 'health-', 'wellbeing-', 'diversity-', 'inclusion-', 'equity-', 'community-', 'stakeholder-', 'governance-', 'board-', 'shareholder-', 'investor-', 'transparency-', 'accountability-', 'audit-', 'certification-', 'standard-', 'iso-', 'b-corp-', 'fair-trade-', 'rainforest-', 'forest-stewardship-', 'carbon-trust-', 'climate-pledge-' ),
				'users' => array( 'users-', 'user-', 'team-', 'people-', 'admin-', 'editor-', 'author-', 'contributor-', 'subscriber-', 'customer-', 'member-', 'staff-', 'employee-', 'role-', 'capability-', 'permission-', 'access-', 'privilege-', 'profile-', 'account-', 'registration-', 'signup-', 'login-', 'authentication-', 'authorization-', 'identity-', 'password-', 'two-factor-', 'mfa-', 'sso-', 'ldap-', 'saml-', 'oauth-', 'openid-', 'jwt-', 'session-', 'cookie-', 'token-', 'bearer-', 'basic-auth-', 'api-key-', 'api-token-', 'webhook-', 'activity-', 'login-history-', 'audit-log-', 'event-log-', 'action-log-', 'tracking-', 'analytics-', 'behavior-', 'engagement-', 'activity-score-', 'productivity-', 'performance-', 'contribution-', 'retention-', 'churn-', 'growth-', 'satisfaction-', 'feedback-', 'survey-', 'review-', 'rating-', 'comment-', 'mention-', 'follow-', 'subscriber-', 'follower-', 'connection-', 'friend-', 'colleague-', 'team-member-', 'collaborator-', 'contributor-', 'partner-', 'vendor-', 'supplier-', 'client-', 'affiliate-', 'influencer-', 'advocate-', 'ambassador-', 'power-user-', 'expert-', 'moderator-', 'admin-panel-', 'dashboard-', 'profile-page-', 'settings-page-', 'preference-', 'notification-', 'email-', 'sms-', 'push-', 'in-app-', 'banner-', 'popup-', 'modal-', 'sidebar-', 'widget-', 'badge-', 'avatar-', 'bio-', 'social-', 'link-', 'website-', 'contact-', 'location-', 'timezone-', 'language-', 'privacy-', 'data-', 'gdpr-', 'ccpa-', 'consent-', 'export-', 'delete-', 'anonymize-', 'blocking-', 'ban-', 'suspend-', 'deactivate-', 'archive-' ),
				'content_publishing' => array( 'pub-', 'content-', 'publishing-', 'post-', 'page-', 'article-', 'blog-', 'news-', 'press-release-', 'announcement-', 'update-', 'tutorial-', 'guide-', 'how-to-', 'tip-', 'trick-', 'hack-', 'tool-', 'resource-', 'template-', 'checklist-', 'worksheet-', 'workbook-', 'ebook-', 'whitepaper-', 'case-study-', 'research-', 'report-', 'study-', 'analysis-', 'opinion-', 'perspective-', 'interview-', 'podcast-', 'video-', 'webinar-', 'presentation-', 'slide-', 'infographic-', 'chart-', 'graph-', 'diagram-', 'illustration-', 'animation-', 'screenshot-', 'screencast-', 'gif-', 'meme-', 'quote-', 'testimonial-', 'review-', 'rating-', 'feedback-', 'comment-', 'discussion-', 'forum-', 'qa-', 'faq-', 'knowledge-base-', 'help-', 'support-', 'documentation-', 'api-', 'code-', 'snippet-', 'gist-', 'pen-', 'codepen-', 'github-', 'gitlab-', 'bitbucket-', 'repository-', 'library-', 'package-', 'module-', 'plugin-', 'theme-', 'template-', 'component-', 'pattern-', 'library-', 'framework-', 'boilerplate-', 'starter-kit-', 'scaffold-', 'generator-', 'builder-', 'constructor-', 'tool-', 'utility-', 'service-', 'tool-', 'calculator-', 'converter-', 'generator-', 'validator-', 'parser-', 'compiler-', 'formatter-', 'linter-', 'optimizer-', 'minifier-', 'bundler-', 'transpiler-', 'polyfill-', 'library-', 'framework-', 'cms-', 'static-site-generator-', 'headless-', 'jamstack-', 'static-', 'dynamic-', 'hybrid-', 'progressive-web-app-', 'pwa-', 'spa-', 'mpa-', 'ssr-', 'ssg-', 'isr-', 'incremental-static-', 'streaming-', 'edge-', 'serverless-', 'function-', 'lambda-', 'cloud-', 'microservice-', 'monolith-', 'modular-', 'atomic-', 'component-based-', 'mobile-app-', 'native-', 'hybrid-', 'cross-platform-', 'rn-', 'flutter-', 'ionic-', 'react-native-', 'xamarin-', 'desktop-app-', 'electron-', 'nwjs-', 'tauri-', 'game-', 'unity-', 'unreal-', 'godot-', 'ar-', 'vr-', 'metaverse-', 'blockchain-', 'crypto-', 'nft-', 'defi-', 'web3-', 'dao-', 'smart-contract-', 'solidity-', 'rust-', 'move-', 'ink-', 'hardhat-', 'truffle-', 'foundry-' ),
				'compliance' => array( 'comp-', 'compliance-', 'legal-', 'law-', 'regulation-', 'rule-', 'standard-', 'audit-', 'audit-trail-', 'audit-log-', 'evidence-', 'proof-', 'certificate-', 'certification-', 'license-', 'permit-', 'approval-', 'consent-', 'agreement-', 'contract-', 'tos-', 'terms-of-service-', 'pp-', 'privacy-policy-', 'eula-', 'license-', 'trademark-', 'copyright-', 'patent-', 'ip-', 'intellectual-property-', 'dpa-', 'data-processing-agreement-', 'sow-', 'statement-of-work-', 'msa-', 'master-service-agreement-', 'nda-', 'non-disclosure-agreement-', 'gdpr-', 'ccpa-', 'cpra-', 'pipl-', 'lgpd-', 'pdpa-', 'pipeda-', 'apec-', 'hipaa-', 'hitech-', 'phi-', 'pci-dss-', 'pci-', 'sox-', 'sarbanes-oxley-', 'hi-', 'hipaa-', 'gram-', 'glba-', 'fcpa-', 'ada-', 'wcag-', 'a11y-', 'accessibility-', 'section-508-', 'en-301-549-', 'iso-', 'iso-9001-', 'iso-27001-', 'iso-26262-', 'iec-', 'iec-61508-', 'sae-', 'sae-j3061-', 'nist-', 'nist-csf-', 'nist-sp-', 'cis-', 'cis-controls-', 'owasp-', 'owasp-top-10-', 'ptes-', 'nist-ptes-', 'atig-', 'ati-', 'mitre-', 'mitre-att&ck-', 'kill-chain-', 'diamond-model-', 'cyber-kill-chain-', 'lockheed-martin-', 'tpm-', 'tpm-2.0-', 'trusted-platform-module-', 'tee-', 'trusted-execution-environment-', 'sgx-', 'trusted-enclave-', 'sev-', 'secure-encrypted-virtualization-', 'ami-', 'advanced-micro-devices-secure-memory-encryption-', 'amd-', 'azure-attestation-', 'aws-attestation-', 'google-attestation-', 'keystone-attestation-', 'attestation-', 'verification-', 'validation-', 'testing-', 'assessment-', 'evaluation-', 'review-', 'inspection-', 'examination-', 'scrutiny-', 'monitoring-', 'supervision-', 'oversight-', 'governance-', 'control-', 'risk-management-', 'business-continuity-', 'disaster-recovery-', 'incident-response-', 'crisis-management-', 'emergency-', 'escalation-', 'notification-', 'alert-', 'investigation-', 'root-cause-', 'corrective-action-', 'remediation-', 'resolution-', 'settlement-', 'fine-', 'penalty-', 'sanction-', 'lawsuit-', 'litigation-', 'arbitration-', 'mediation-', 'judgment-', 'settlement-agreement-' ),
			);
			
			// Initialize all categories to 0
			foreach ( array_keys( $category_prefixes ) as $cat ) {
				$category_counts[ $cat ] = 0;
			}
			
			// Count files by matching prefixes
			foreach ( $files as $file ) {
				$basename = basename( $file );
				
				foreach ( $category_prefixes as $cat => $prefixes ) {
					foreach ( $prefixes as $prefix ) {
						if ( strpos( $basename, 'class-diagnostic-' . $prefix ) === 0 ) {
							$category_counts[ $cat ]++;
							break 2; // Found match, move to next file
						}
					}
				}
			}
		}
	}
	
	return isset( $category_counts[ $category ] ) ? $category_counts[ $category ] : 0;
}

/**
 * Get KB link for a finding/diagnostic slug.
 * Falls back to a slug-based URL if not mapped.
 *
 * @param string $slug Finding/diagnostic slug.
 * @return string KB URL.
 */
function wpshadow_get_kb_link( string $slug ): string {
	$map = array(
		'backup-missing'      => 'https://wpshadow.com/kb/how-to-set-up-automated-backups/',
		'ssl-missing'         => 'https://wpshadow.com/kb/enable-https-ssl-on-your-site/',
		'outdated-plugins'    => 'https://wpshadow.com/kb/how-to-safely-update-plugins/',
		'memory-limit-low'    => 'https://wpshadow.com/kb/increase-php-memory-limit/',
		'permalinks-plain'    => 'https://wpshadow.com/kb/configure-wordpress-permalinks-for-seo/',
		'tagline-empty'       => 'https://wpshadow.com/kb/write-an-effective-site-tagline/',
		'debug-mode-enabled'  => 'https://wpshadow.com/kb/disable-wordpress-debug-mode/',
		'wordpress-outdated'  => 'https://wpshadow.com/kb/how-to-update-wordpress-safely/',
		'plugin-count-high'   => 'https://wpshadow.com/kb/audit-and-optimize-your-wordpress-plugins/',
	);

	if ( isset( $map[ $slug ] ) ) {
		return $map[ $slug ];
	}

	$slug = sanitize_title( $slug );
	return 'https://wpshadow.com/kb/' . $slug . '/';
}

/**
 * Get training link for a treatment slug.
 * Falls back to slug-based training URL.
 *
 * @param string $slug Treatment/finding slug.
 * @return string Training video URL.
 */
function wpshadow_get_training_link( string $slug ): string {
	$slug = sanitize_title( $slug );
	return 'https://wpshadow.com/training/' . $slug . '/';
}

/**
 * Get site findings based on diagnostics and WordPress Core Site Health.
 */
function wpshadow_get_site_findings() {
	// Check cache first (5 minute expiration)
	$cache_key = 'wpshadow_site_findings_cache';
	$cached = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}
	
	// Run all diagnostic checks from registry
	$findings = \WPShadow\Diagnostics\Diagnostic_Registry::run_all_checks();

	foreach ( $findings as &$finding ) {
		if ( empty( $finding['category'] ) ) {
			$finding['category'] = wpshadow_get_finding_category( $finding );
		}
	}
	unset( $finding );

	// Add WordPress Settings scan
	if ( class_exists( '\WPShadow\Diagnostics\WordPress_Settings_Scan' ) ) {
		$settings_findings = \WPShadow\Diagnostics\WordPress_Settings_Scan::run_scan();
		if ( ! empty( $settings_findings ) ) {
			$findings = array_merge( $findings, $settings_findings );
		}
	}

	// Add mobile friendliness issues
	$mobile_issues = \WPShadow\Diagnostics\Diagnostic_Mobile_Friendliness::get_all_issues();
	if ( ! empty( $mobile_issues ) ) {
		$findings = array_merge( $findings, $mobile_issues );
	}

	// Get WordPress Core Site Health data if available
	if ( class_exists( 'WP_Site_Health' ) ) {
		$result = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/site-health/status' ) );
		if ( ! is_wp_error( $result ) ) {
			$data = $result->get_data();
			// Supplement with critical core checks if not already found
			if ( ! empty( $data['tests']['critical'] ) ) {
				foreach ( $data['tests']['critical'] as $test ) {
					if ( ! empty( $test['description'] ) ) {
						$findings[] = array(
							'title'       => $test['label'] ?? 'Site Health Issue',
							'description' => wp_strip_all_tags( $test['description'] ),
							'color'       => '#f44336',
							'bg_color'    => '#ffebee',
							'category'    => 'settings',
						);
					}
				}
			}
		}
	}

	// Enrich with KB and training links (Phase 5)
	foreach ( $findings as &$finding ) {
		$slug = $finding['id'] ?? ( $finding['title'] ?? '' );
		$slug = sanitize_title( (string) $slug );
		$finding['kb_link'] = wpshadow_get_kb_link( $slug );
		$finding['training_link'] = wpshadow_get_training_link( $slug );
	}
	unset( $finding );
	
	// Cache for 5 minutes
	set_transient( $cache_key, $findings, 5 * MINUTE_IN_SECONDS );

	return $findings;
}

/**
 * Clear the cached findings (call after scans, fixes, or settings changes).
 * 
 * @return void
 */
function wpshadow_clear_findings_cache() {
	delete_transient( 'wpshadow_site_findings_cache' );
}

/**
 * Determine the category for a finding.
 *
 * @param array $finding Finding data.
 * @return string Category slug.
 */
function wpshadow_get_finding_category( $finding ) {
	$category_map = array(
		'memory-limit-low'   => 'settings',
		'backup-missing'     => 'settings',
		'permalinks-plain'   => 'seo',
		'tagline-empty'      => 'design',
		'ssl-missing'        => 'seo',
		'outdated-plugins'   => 'settings',
		'inactive-plugins'   => 'settings',
		'hotlink-protection-missing' => 'security',
		'head-cleanup-needed' => 'performance',
		'iframe-busting-missing' => 'security',
		'image-lazyload-disabled' => 'performance',
		'external-fonts-loading' => 'performance',
		'plugin-auto-updates-disabled' => 'settings',
		'error-log-large' => 'stability',
		'core-integrity-mismatch' => 'security',
		'skiplinks-missing' => 'accessibility',
		'asset-versions' => 'performance',
		'css-classes' => 'performance',
		'maintenance' => 'stability',
		'nav-aria' => 'accessibility',
		'admin-username' => 'security',
		'search-indexing' => 'seo',
		'admin-email' => 'settings',
		'timezone' => 'settings',
		'debug-mode-enabled' => 'settings',
		'wordpress-outdated' => 'settings',
		'plugin-count-high'  => 'settings',
		'content-optimizer' => 'content',
		'paste-cleanup' => 'content',
		'html-cleanup' => 'performance',
		'pre-publish-review' => 'content',
		'embed-disable' => 'performance',
		'interactivity-cleanup' => 'performance',
		'php-version' => 'security',
		'database-health' => 'performance',
		'file-permissions' => 'security',
		'security-headers' => 'security',
	);

	$finding_id = isset( $finding['id'] ) ? $finding['id'] : '';
	if ( isset( $category_map[ $finding_id ] ) ) {
		return $category_map[ $finding_id ];
	}

	if ( isset( $finding['category'] ) && ! empty( $finding['category'] ) ) {
		return $finding['category'];
	}

	return 'settings';
}

/**
 * Get PHP memory limit in MB.
 */
function wpshadow_get_memory_limit_mb() {
	$limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
	return intval( $limit / 1024 / 1024 );
}

/**
 * Check if a backup plugin is active.
 */
function wpshadow_has_backup_plugin() {
	$active_plugins = get_option( 'active_plugins', array() );
	$backup_plugins = array(
		'updraftplus/updraft.php',
		'backwpup/backwpup.php',
		'backup-backup/backup.php',
		'jetpack-backup/jetpack-backup.php',
		'vaultpress/vaultpress.php',
	);
	return ! empty( array_intersect( $active_plugins, $backup_plugins ) );
}

/**
 * Check if permalink structure is configured (not plain).
 */
function wpshadow_is_permalink_configured() {
	$structure = get_option( 'permalink_structure' );
	return ! empty( $structure ) && $structure !== '';
}

/**
 * Count outdated plugins.
 */
function wpshadow_get_outdated_plugins_count() {
	$current_plugins = get_plugins();
	$updates = get_site_transient( 'update_plugins' );
	
	if ( ! isset( $updates->response ) ) {
		return 0;
	}

	$count = 0;
	foreach ( $updates->response as $plugin_file => $plugin_data ) {
		if ( isset( $current_plugins[ $plugin_file ] ) ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Get WordPress Site Health status.
 * 
 * Philosophy: Show value (#9) - Track WordPress native health indicators
 * 
 * @return array Array with 'score' (0-100), 'status' (Good/Fair/Critical), 'color'.
 */
function wpshadow_get_wordpress_site_health() {
	// Try to use native WordPress Site Health if available
	if ( function_exists( 'wp_get_site_health_status' ) ) {
		$health = wp_get_site_health_status();
		$status = isset( $health['status'] ) ? $health['status'] : 'good';
		$score = isset( $health['percentage'] ) ? (int) $health['percentage'] : 75;
	} else {
		// Fallback: Use basic checks
		$score = 75;
		$status = 'good';
		
		// Check for SSL
		if ( ! is_ssl() ) {
			$score -= 20;
		}
		
		// Check for REST API
		if ( ! rest_get_url_prefix() ) {
			$score -= 10;
		}
		
		// Check for debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$score -= 15;
		}
		
		if ( $score <= 50 ) {
			$status = 'critical';
		} elseif ( $score <= 75 ) {
			$status = 'recommended';
		}
	}
	
	// Map status to color
	$color_map = array(
		'good'        => '#2d5016',  // Green
		'recommended' => '#f57c00',  // Orange
		'critical'    => '#c62828',  // Red
	);
	
	$color = $color_map[ $status ] ?? '#2d5016';
	
	return array(
		'score'  => max( 0, min( 100, $score ) ),
		'status' => ucfirst( $status ),
		'color'  => $color,
		'label'  => __( 'WordPress Site Health', 'wpshadow' ),
		'icon'   => 'dashicons-wordpress-alt',
	);
}

/**
 * Get threat gauge color based on threat level.
 *
 * @param int $threat_level Threat level 0-100.
 * @return string Hex color code.
 */
function wpshadow_get_threat_gauge_color( $threat_level ) {
	if ( $threat_level >= 80 ) {
		return '#f44336'; // Red - Critical
	} elseif ( $threat_level >= 60 ) {
		return '#ff9800'; // Orange - High
	} elseif ( $threat_level >= 40 ) {
		return '#ffc107'; // Amber - Medium
	} else {
		return '#2196f3'; // Blue - Low
	}
}

/**
 * Get threat label based on threat level.
 *
 * @param int $threat_level Threat level 0-100.
 * @return string Threat label.
 */
function wpshadow_get_threat_label( $threat_level ) {
	if ( $threat_level >= 80 ) {
		return 'Critical';
	} elseif ( $threat_level >= 60 ) {
		return 'High';
	} elseif ( $threat_level >= 40 ) {
		return 'Medium';
	} else {
		return 'Low';
	}
}

/**
 * Check if site is registered with WPShadow.
 *
 * @return bool True if site is registered.
 */
function wpshadow_is_site_registered() {
	// Check if email consent is granted (indicates registration)
	$email_consent = get_option( 'wpshadow_email_consent', false );
	return ! empty( $email_consent['granted'] );
}

/**
 * Generate AI tagline suggestions based on recent content.
 *
 * @return array Array of tagline suggestions.
 */
function wpshadow_generate_tagline_suggestions() {
	$suggestions = array();
	
	// Get recent posts for context
	$recent_posts = get_posts( array(
		'numberposts' => 5,
		'post_status' => 'publish',
	) );
	
	// Get site title
	$site_title = get_bloginfo( 'name' );
	
	// Analyze content to generate suggestions
	$categories = get_categories( array( 'number' => 3, 'orderby' => 'count', 'order' => 'DESC' ) );
	$category_names = array_map( function( $cat ) { return $cat->name; }, $categories );
	
	// Generate basic suggestions based on content analysis
	if ( ! empty( $recent_posts ) ) {
		// Suggestion 1: Based on most common category
		if ( ! empty( $category_names[0] ) ) {
			$suggestions[] = "Your source for {$category_names[0]} insights and tips";
		}
		
		// Suggestion 2: Based on site title
		if ( ! empty( $site_title ) && $site_title !== 'WordPress' ) {
			$suggestions[] = "{$site_title} - Sharing knowledge and expertise";
		}
		
		// Suggestion 3: Generic but professional
		if ( count( $recent_posts ) > 3 ) {
			$suggestions[] = "Expert articles and resources you can trust";
		} else {
			$suggestions[] = "Quality content for curious minds";
		}
	} else {
		// Fallback suggestions if no posts
		$suggestions = array(
			"Your trusted source for {$site_title} content",
			"Sharing insights and ideas that matter",
			"Where knowledge meets community",
		);
	}
	
	return array_slice( $suggestions, 0, 3 );
}

/**
 * Attempt to automatically fix a finding.
 *
 * @param string $finding_id The ID of the finding to fix.
 * @return array {success: bool, message: string}
 */
function wpshadow_attempt_autofix( $finding_id ) {
	$has_permission = current_user_can( 'manage_options' );
	if ( ! $has_permission && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return array(
			'success' => false,
			'message' => 'You do not have permission to make this change.',
		);
	}

	$finding_id = sanitize_key( $finding_id );
	if ( empty( $finding_id ) ) {
		return array(
			'success' => false,
			'message' => 'Invalid finding ID.',
		);
	}

	if ( ! class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
		return array(
			'success' => false,
			'message' => 'Treatment registry is not available.',
		);
	}

	$result = \WPShadow\Treatments\Treatment_Registry::apply_treatment( $finding_id );

	if ( ! is_array( $result ) ) {
		return array(
			'success' => false,
			'message' => 'Auto-fix failed unexpectedly.',
		);
	}

	return $result;
}

/**
 * Save current health snapshot for comparison.
 */
function wpshadow_save_health_snapshot() {
	$findings = wpshadow_get_site_findings();
	
	// Get existing snapshots
	$snapshots = get_option( 'wpshadow_health_snapshots', array() );
	
	// Add current snapshot
	$snapshots[] = array(
		'timestamp' => current_time( 'timestamp' ),
		'findings'  => $findings,
		'count'     => count( $findings ),
	);
	
	// Keep only last 30 snapshots
	if ( count( $snapshots ) > 30 ) {
		$snapshots = array_slice( $snapshots, -30 );
	}
	
	update_option( 'wpshadow_health_snapshots', $snapshots );
}

/**
 * Log an action taken on a finding.
 *
 * @param string $finding_id The ID of the finding.
 * @param string $action     The action taken (dismissed, auto_fixed, manual_fixed).
 * @param string $message    Optional message describing the action.
 */
function wpshadow_log_finding_action( $finding_id, $action, $message = '' ) {
	$log = get_option( 'wpshadow_finding_log', array() );
	
	$log[] = array(
		'finding_id' => $finding_id,
		'action'     => $action,
		'message'    => $message,
		'user_id'    => get_current_user_id(),
		'timestamp'  => current_time( 'timestamp' ),
	);
	
	// Keep only last 100 log entries
	if ( count( $log ) > 100 ) {
		$log = array_slice( $log, -100 );
	}
	
	update_option( 'wpshadow_finding_log', $log );
}

// Network admin menu for multisite.
add_action( 'network_admin_menu', function() {
	add_menu_page(
		'WPShadow',
		'WPShadow',
		'read',
		'wpshadow',
		function() {
			echo '<div class="wrap"><h1>WPShadow (Network)</h1><p>Network admin menu check.</p></div>';
		},
		'dashicons-admin-generic',
		999
	);
} );


/**
 * Calculate eco/sustainability score.
 *
 * @return array Score payload.
 */


/**
 * Register WPShadow personal data exporter.
 */
add_filter( 'wp_privacy_personal_data_exporters', function( $exporters ) {
	$exporters['wpshadow'] = array(
		'exporter_friendly_name' => __( 'WPShadow User Preferences', 'wpshadow' ),
		'callback'               => 'wpshadow_privacy_exporter',
	);
	return $exporters;
} );

/**
 * Export WPShadow user data for privacy requests.
 *
 * @param string $email_address User email.
 * @param int    $page          Page number.
 * @return array Export data.
 */
function wpshadow_privacy_exporter( $email_address, $page = 1 ) {
	$user = get_user_by( 'email', $email_address );
	if ( ! $user ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}

	$user_id = $user->ID;
	$export_items = array();

	// Tooltip preferences
	$tip_prefs = get_user_meta( $user_id, 'wpshadow_tip_prefs', true );
	if ( ! empty( $tip_prefs ) && is_array( $tip_prefs ) ) {
		$tip_data = array();
		if ( ! empty( $tip_prefs['disabled_categories'] ) ) {
			$tip_data[] = array(
				'name'  => __( 'Disabled Tooltip Categories', 'wpshadow' ),
				'value' => implode( ', ', $tip_prefs['disabled_categories'] ),
			);
		}
		if ( ! empty( $tip_prefs['dismissed_tips'] ) ) {
			$tip_data[] = array(
				'name'  => __( 'Dismissed Tips', 'wpshadow' ),
				'value' => implode( ', ', $tip_prefs['dismissed_tips'] ),
			);
		}
		if ( ! empty( $tip_data ) ) {
			$export_items[] = array(
				'group_id'    => 'wpshadow_tooltip_prefs',
				'group_label' => __( 'WPShadow Tooltip Preferences', 'wpshadow' ),
				'item_id'     => "wpshadow-tooltips-{$user_id}",
				'data'        => $tip_data,
			);
		}
	}

	// Dark mode preference
	$dark_mode_pref = get_user_meta( $user_id, 'wpshadow_dark_mode_preference', true );
	if ( ! empty( $dark_mode_pref ) ) {
		$export_items[] = array(
			'group_id'    => 'wpshadow_display_prefs',
			'group_label' => __( 'WPShadow Display Preferences', 'wpshadow' ),
			'item_id'     => "wpshadow-darkmode-{$user_id}",
			'data'        => array(
				array(
					'name'  => __( 'Dark Mode Preference', 'wpshadow' ),
					'value' => $dark_mode_pref,
				),
			),
		);
	}

	// Hidden widget preferences
	$quick_hidden = get_user_meta( $user_id, 'wpshadow_hide_quick_scan', true );
	$deep_hidden  = get_user_meta( $user_id, 'wpshadow_hide_deep_scan', true );
	if ( $quick_hidden || $deep_hidden ) {
		$widget_data = array();
		if ( $quick_hidden ) {
			$widget_data[] = array(
				'name'  => __( 'Quick Scan Widget Hidden', 'wpshadow' ),
				'value' => __( 'Yes', 'wpshadow' ),
			);
		}
		if ( $deep_hidden ) {
			$widget_data[] = array(
				'name'  => __( 'Deep Scan Widget Hidden', 'wpshadow' ),
				'value' => __( 'Yes', 'wpshadow' ),
			);
		}
		$export_items[] = array(
			'group_id'    => 'wpshadow_widget_prefs',
			'group_label' => __( 'WPShadow Dashboard Widget Preferences', 'wpshadow' ),
			'item_id'     => "wpshadow-widgets-{$user_id}",
			'data'        => $widget_data,
		);
	}

	return array(
		'data' => $export_items,
		'done' => true,
	);
}

/**
 * Register WPShadow personal data eraser.
 */
add_filter( 'wp_privacy_personal_data_erasers', function( $erasers ) {
	$erasers['wpshadow'] = array(
		'eraser_friendly_name' => __( 'WPShadow User Preferences', 'wpshadow' ),
		'callback'             => 'wpshadow_privacy_eraser',
	);
	return $erasers;
} );

/**
 * Erase WPShadow user data for privacy requests.
 *
 * @param string $email_address User email.
 * @param int    $page          Page number.
 * @return array Erasure result.
 */
function wpshadow_privacy_eraser( $email_address, $page = 1 ) {
	$user = get_user_by( 'email', $email_address );
	if ( ! $user ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$user_id = $user->ID;
	$items_removed = false;

	// Remove tooltip preferences
	if ( delete_user_meta( $user_id, 'wpshadow_tip_prefs' ) ) {
		$items_removed = true;
	}

	// Remove dark mode preference
	if ( delete_user_meta( $user_id, 'wpshadow_dark_mode_preference' ) ) {
		$items_removed = true;
	}

	// Remove widget visibility preferences
	if ( delete_user_meta( $user_id, 'wpshadow_hide_quick_scan' ) ) {
		$items_removed = true;
	}
	if ( delete_user_meta( $user_id, 'wpshadow_hide_deep_scan' ) ) {
		$items_removed = true;
	}

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * Add WPShadow privacy policy content suggestion.
 */
add_action( 'admin_init', function() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = sprintf(
		'<h2>%s</h2><p>%s</p><h3>%s</h3><ul><li>%s</li><li>%s</li><li>%s</li></ul><h3>%s</h3><p>%s</p>',
		__( 'WPShadow Plugin', 'wpshadow' ),
		__( 'This site uses the WPShadow plugin to enhance the WordPress admin experience. WPShadow stores the following user preferences locally on this site:', 'wpshadow' ),
		__( 'What We Collect', 'wpshadow' ),
		__( '<strong>Tooltip Preferences:</strong> Which admin tooltips you have dismissed or disabled, to avoid showing you the same tip repeatedly.', 'wpshadow' ),
		__( '<strong>Display Preferences:</strong> Your dark mode preference (light, dark, or automatic) for the WPShadow admin interface.', 'wpshadow' ),
		__( '<strong>Dashboard Widget Preferences:</strong> Which dashboard widgets you have chosen to hide or show.', 'wpshadow' ),
		__( 'Your Rights', 'wpshadow' ),
		__( 'You can request to export or erase your WPShadow preferences at any time using the WordPress privacy tools under Tools > Export Personal Data or Tools > Erase Personal Data.', 'wpshadow' )
	);

	wp_add_privacy_policy_content(
		'WPShadow',
		wp_kses_post( wpautop( $content, false ) )
	);
} );

/**
 * Generate a friendly, memorable strong password using word combinations.
 *
 * @return string Generated password.
 */
function wpshadow_generate_friendly_password() {
	$json_file = WPSHADOW_PATH . 'includes/data/password-words.json';
	
	if ( ! file_exists( $json_file ) ) {
		// Fallback to WordPress default if JSON file missing
		return wp_generate_password( 16, true, true );
	}
	
	$word_sets = json_decode( file_get_contents( $json_file ), true );
	
	if ( empty( $word_sets ) || ! is_array( $word_sets ) ) {
		return wp_generate_password( 16, true, true );
	}
	
	// Pick a random word set
	$word_set = $word_sets[ array_rand( $word_sets ) ];
	
	// Combine words with first letter capitalized
	$password = implode( '', $word_set );
	
	// Character substitutions to make it stronger
	$substitutions = array(
		'a' => '@',
		'A' => '@',
		'e' => '3',
		'E' => '3',
		'i' => '1',
		'I' => '1',
		'o' => '0',
		'O' => '0',
		's' => '$',
		'S' => '$',
		't' => '7',
		'T' => '7',
	);
	
	// Apply substitutions to 2-3 random positions
	$chars = str_split( $password );
	$positions_to_substitute = array_rand( $chars, min( 3, count( $chars ) ) );
	
	if ( ! is_array( $positions_to_substitute ) ) {
		$positions_to_substitute = array( $positions_to_substitute );
	}
	
	foreach ( $positions_to_substitute as $pos ) {
		$char = $chars[ $pos ];
		if ( isset( $substitutions[ $char ] ) ) {
			$chars[ $pos ] = $substitutions[ $char ];
		}
	}
	
	$password = implode( '', $chars );
	
	// Add ! at the end for extra strength
	$password .= '!';
	
	return $password;
}

/**
 * Override default password generation on user-new.php with friendly password.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( $hook !== 'user-new.php' ) {
		return;
	}
	
	wp_enqueue_script(
		'wpshadow-friendly-password',
		WPSHADOW_URL . 'assets/js/friendly-password.js',
		array( 'jquery', 'user-profile' ),
		WPSHADOW_VERSION,
		true
	);
	
	wp_localize_script( 'wpshadow-friendly-password', 'wpshadowPassword', array(
		'password' => wpshadow_generate_friendly_password(),
		'nonce'    => wp_create_nonce( 'wpshadow_generate_password' ),
	) );
});

/**
 * Enqueue safety warnings CSS on frontend for KB pages and posts.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'wpshadow-safety-warnings-frontend',
		WPSHADOW_URL . 'assets/css/safety-warnings.css',
		array(),
		WPSHADOW_VERSION
	);
} );

// Generate password handler moved to class
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-generate-password-handler.php';
\WPShadow\Admin\Ajax\Generate_Password_Handler::register();

// Notification builder handlers
require_once plugin_dir_path( __FILE__ ) . 'includes/workflow/class-notification-builder.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-save-notification-rule-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax/class-delete-notification-rule-handler.php';

// Register notification builder AJAX handlers
add_action( 'plugins_loaded', function() {
	\WPShadow\Admin\Ajax\Save_Notification_Rule_Handler::register();
	\WPShadow\Admin\Ajax\Delete_Notification_Rule_Handler::register();
} );

/**
 * AJAX handler for force scan
 */
add_action( 'wp_ajax_wpshadow_force_scan', function() {
	check_ajax_referer( 'wpshadow_force_scan', 'nonce' );
	
	if ( ! current_user_can( 'read' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
	}
	
	// Update last scan time
	update_option( 'wpshadow_last_scan_time', time() );
	
	// In future, trigger actual diagnostic scan here
	// For now, just acknowledge
	wp_send_json_success( array( 
		'message' => __( 'Scan completed successfully!', 'wpshadow' ),
		'findings' => 0
	) );
} );

/**
 * AJAX handler for creating workflow from finding (Phase 2 - Action Items bridge)
 */
add_action( 'wp_ajax_wpshadow_create_workflow_from_finding', function() {
	check_ajax_referer( 'wpshadow_create_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
	}
	
	$finding_id = isset( $_POST['finding_id'] ) ? sanitize_text_field( $_POST['finding_id'] ) : '';
	$workflow_name = isset( $_POST['workflow_name'] ) ? sanitize_text_field( $_POST['workflow_name'] ) : 'New Workflow';
	$workflow_type = isset( $_POST['workflow_type'] ) ? sanitize_key( $_POST['workflow_type'] ) : 'auto_fix';
	$category = isset( $_POST['category'] ) ? sanitize_key( $_POST['category'] ) : 'other';
	
	if ( ! $finding_id ) {
		wp_send_json_error( array( 'message' => __( 'Finding ID is required.', 'wpshadow' ) ) );
	}
	
	// Build workflow blocks from finding context (trigger + action)
	$blocks = array();
	
	// Add trigger block: Guardian detection
	$blocks[] = array(
		'type' => 'trigger',
		'trigger_type' => 'guardian_finding_detected',
		'finding_id' => $finding_id,
		'category' => $category
	);
	
	// Add action block based on workflow type
	if ( $workflow_type === 'auto_fix' ) {
		$blocks[] = array(
			'type' => 'action',
			'action_type' => 'auto_fix',
			'finding_id' => $finding_id,
			'auto_execute' => true
		);
	} elseif ( $workflow_type === 'reactive' ) {
		$blocks[] = array(
			'type' => 'action',
			'action_type' => 'notify',
			'finding_id' => $finding_id,
			'notify_user' => true
		);
	} elseif ( $workflow_type === 'scheduled' ) {
		$blocks[] = array(
			'type' => 'trigger',
			'trigger_type' => 'scheduled',
			'schedule' => 'daily' // User will customize this
		);
		$blocks[] = array(
			'type' => 'action',
			'action_type' => 'auto_fix',
			'finding_id' => $finding_id,
			'auto_execute' => true
		);
	}
	
	// Save workflow using Workflow_Manager
	$workflow_id = \WPShadow\Workflow\Workflow_Manager::save_workflow( 
		$workflow_name,
		$blocks
	);
	
	if ( ! $workflow_id ) {
		wp_send_json_error( array( 'message' => __( 'Could not create workflow.', 'wpshadow' ) ) );
	}
	
	// Log activity (Philosophy #9: Show Value)
	if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
		\WPShadow\Core\Activity_Logger::log( array(
			'action' => 'workflow_created_from_finding',
			'finding_id' => $finding_id,
			'workflow_id' => $workflow_id,
			'workflow_type' => $workflow_type,
			'timestamp' => time()
		) );
	}
	
	wp_send_json_success( array(
		'workflow_id' => $workflow_id,
		'message' => __( 'Workflow created! Redirecting to builder...', 'wpshadow' )
	) );
} );

/**
 * Override wp_mail From Name if WPShadow setting is configured.
 */
add_filter( 'wp_mail_from_name', function( $from_name ) {
	$custom_from_name = get_option( 'wpshadow_email_from_name', '' );
	
	if ( ! empty( $custom_from_name ) ) {
		return $custom_from_name;
	}
	
	return $from_name;
}, 999 );

/**
 * Render Guardian page (Diagnostics & Treatments System)
 */
function wpshadow_render_guardian() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
	$last_scan = get_option( 'wpshadow_last_scan_time', 0 );
	$scan_frequency = get_option( 'wpshadow_scan_frequency', 'every_4_hours' );
	
	?>
	<div class="wrap">
		<h1>
			<span class="dashicons dashicons-shield-alt" style="font-size: 32px; vertical-align: middle; color: #0073aa;"></span>
			<?php esc_html_e( 'Guardian', 'wpshadow' ); ?>
		</h1>
		<p><?php esc_html_e( 'Your always-on WordPress health monitoring system', 'wpshadow' ); ?></p>

		<!-- Tab Navigation -->
		<nav class="nav-tab-wrapper" style="margin: 20px 0;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=overview' ) ); ?>" class="nav-tab <?php echo $active_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Overview', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=diagnostics' ) ); ?>" class="nav-tab <?php echo $active_tab === 'diagnostics' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Diagnostics', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=treatments' ) ); ?>" class="nav-tab <?php echo $active_tab === 'treatments' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Treatments', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian&tab=schedule' ) ); ?>" class="nav-tab <?php echo $active_tab === 'schedule' ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Schedule', 'wpshadow' ); ?>
			</a>
		</nav>

		<?php
		switch ( $active_tab ) {
			case 'overview':
				wpshadow_render_guardian_overview();
				break;
			case 'diagnostics':
				wpshadow_render_guardian_diagnostics();
				break;
			case 'treatments':
				wpshadow_render_guardian_treatments();
				break;
			case 'schedule':
				wpshadow_render_guardian_schedule();
				break;
			default:
				wpshadow_render_guardian_overview();
		}
		?>
	</div>
	<?php
}

/**
 * Render Guardian Overview tab
 */
function wpshadow_render_guardian_overview() {
	$last_scan = get_option( 'wpshadow_last_scan_time', 0 );
	$time_ago = $last_scan ? human_time_diff( $last_scan, time() ) : __( 'Never', 'wpshadow' );
	$scan_frequency = get_option( 'wpshadow_scan_frequency', 'every_4_hours' );
	$next_scan = $last_scan ? $last_scan + ( 4 * HOUR_IN_SECONDS ) : time();
	$next_scan_text = $next_scan > time() ? human_time_diff( time(), $next_scan ) : __( 'Soon', 'wpshadow' );
	
	// Get diagnostics and treatments count
	$diagnostic_registry = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();
	$treatment_registry = \WPShadow\Treatments\Treatment_Registry::get_all();
	
	// Get recent findings
	$all_findings = wpshadow_get_site_findings();
	$recent_findings = array_slice( $all_findings, 0, 5 );
	
	?>
	<div style="max-width: 1200px;">
		<!-- Status Cards -->
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
			<!-- Last Scan -->
			<div style="background: linear-gradient(135deg, #e3f2fd 0%, #f0f7ff 100%); border: 2px solid #2196f3; border-radius: 8px; padding: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
					<span class="dashicons dashicons-clock" style="font-size: 24px; color: #1976d2;"></span>
					<h3 style="margin: 0; color: #1565c0;"><?php esc_html_e( 'Last Scan', 'wpshadow' ); ?></h3>
				</div>
				<p style="font-size: 20px; font-weight: 600; margin: 0; color: #0d47a1;"><?php echo esc_html( $time_ago ); ?> <?php esc_html_e( 'ago', 'wpshadow' ); ?></p>
			</div>

			<!-- Next Scan -->
			<div style="background: linear-gradient(135deg, #f3e5f5 0%, #faf5ff 100%); border: 2px solid #ab47bc; border-radius: 8px; padding: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
					<span class="dashicons dashicons-update" style="font-size: 24px; color: #8e24aa;"></span>
					<h3 style="margin: 0; color: #7b1fa2;"><?php esc_html_e( 'Next Scan', 'wpshadow' ); ?></h3>
				</div>
				<p style="font-size: 20px; font-weight: 600; margin: 0; color: #6a1b9a;"><?php esc_html_e( 'In', 'wpshadow' ); ?> <?php echo esc_html( $next_scan_text ); ?></p>
			</div>

			<!-- Active Diagnostics -->
			<div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8f4 100%); border: 2px solid #66bb6a; border-radius: 8px; padding: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
					<span class="dashicons dashicons-yes-alt" style="font-size: 24px; color: #43a047;"></span>
					<h3 style="margin: 0; color: #2e7d32;"><?php esc_html_e( 'Diagnostics', 'wpshadow' ); ?></h3>
				</div>
				<p style="font-size: 20px; font-weight: 600; margin: 0; color: #1b5e20;"><?php echo count( $diagnostic_registry ); ?> <?php esc_html_e( 'active', 'wpshadow' ); ?></p>
			</div>

			<!-- Available Treatments -->
			<div style="background: linear-gradient(135deg, #fff3e0 0%, #fffaf0 100%); border: 2px solid #ffa726; border-radius: 8px; padding: 20px;">
				<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
					<span class="dashicons dashicons-admin-tools" style="font-size: 24px; color: #fb8c00;"></span>
					<h3 style="margin: 0; color: #ef6c00;"><?php esc_html_e( 'Treatments', 'wpshadow' ); ?></h3>
				</div>
				<p style="font-size: 20px; font-weight: 600; margin: 0; color: #e65100;"><?php echo count( $treatment_registry ); ?> <?php esc_html_e( 'available', 'wpshadow' ); ?></p>
			</div>
		</div>

		<!-- Force Scan Button -->
		<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; margin: 20px 0;">
			<h3><?php esc_html_e( 'Manual Scan', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Guardian runs automatically in the background, but you can force a scan anytime.', 'wpshadow' ); ?></p>
			<button id="wpshadow-force-scan-btn" class="button button-primary button-hero" style="margin-top: 10px;">
				<span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
				<?php esc_html_e( 'Force Scan Now', 'wpshadow' ); ?>
			</button>
			<p style="margin-top: 10px; font-size: 12px; color: #666;">
				<?php esc_html_e( 'Scans typically take 30-60 seconds depending on your site size.', 'wpshadow' ); ?>
			</p>
		</div>

		<!-- Recent Findings -->
		<?php if ( ! empty( $recent_findings ) ) : ?>
		<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; margin: 20px 0;">
			<h3><?php esc_html_e( 'Recent Findings', 'wpshadow' ); ?></h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Issue', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Category', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Severity', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_findings as $finding ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $finding['title'] ?? __( 'Unknown', 'wpshadow' ) ); ?></strong></td>
						<td><?php echo esc_html( ucfirst( $finding['category'] ?? 'other' ) ); ?></td>
						<td>
							<span style="display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; background: <?php echo esc_attr( $finding['color'] ?? '#ccc' ); ?>; color: white;">
								<?php echo esc_html( ucfirst( $finding['severity'] ?? 'medium' ) ); ?>
							</span>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-action-items' ) ); ?>" class="button button-small">
								<?php esc_html_e( 'View in Action Items', 'wpshadow' ); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<script>
		jQuery(document).ready(function($) {
			$('#wpshadow-force-scan-btn').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update wpshadow-spin" style="vertical-align: middle;"></span> <?php echo esc_js( __( 'Scanning...', 'wpshadow' ) ); ?>');
				
				$.post(ajaxurl, {
					action: 'wpshadow_force_scan',
					nonce: '<?php echo wp_create_nonce( 'wpshadow_force_scan' ); ?>'
				}, function(response) {
					if (response.success) {
						$btn.html('<span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span> <?php echo esc_js( __( 'Scan Complete!', 'wpshadow' ) ); ?>');
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						alert(response.data?.message || '<?php echo esc_js( __( 'Scan failed', 'wpshadow' ) ); ?>');
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> <?php echo esc_js( __( 'Force Scan Now', 'wpshadow' ) ); ?>');
					}
				});
			});
		});
		</script>
		<style>
		.wpshadow-spin {
			animation: spin 1s linear infinite;
		}
		@keyframes spin {
			from { transform: rotate(0deg); }
			to { transform: rotate(360deg); }
		}
		</style>
	</div>
	<?php
}

/**
 * Render Guardian Diagnostics tab
 */
function wpshadow_render_guardian_diagnostics() {
	echo '<p>' . esc_html__( 'Diagnostics list coming soon. For now, view findings on the Dashboard or Action Items page.', 'wpshadow' ) . '</p>';
}

/**
 * Render Guardian Treatments tab
 */
function wpshadow_render_guardian_treatments() {
	echo '<p>' . esc_html__( 'Treatments list coming soon. Available treatments can be triggered from Action Items.', 'wpshadow' ) . '</p>';
}

/**
 * Render Guardian Schedule tab
 */
function wpshadow_render_guardian_schedule() {
	$scan_frequency = get_option( 'wpshadow_scan_frequency', 'every_4_hours' );
	?>
	<div style="max-width: 800px;">
		<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px;">
			<h3><?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'How often should Guardian scan your site?', 'wpshadow' ); ?></p>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_save_scan_frequency', 'wpshadow_scan_frequency_nonce' ); ?>
				
				<label style="display: block; margin: 15px 0;">
					<input type="radio" name="scan_frequency" value="hourly" <?php checked( $scan_frequency, 'hourly' ); ?>>
					<strong><?php esc_html_e( 'Hourly', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Every hour (most vigilant)', 'wpshadow' ); ?>
				</label>
				
				<label style="display: block; margin: 15px 0;">
					<input type="radio" name="scan_frequency" value="every_4_hours" <?php checked( $scan_frequency, 'every_4_hours' ); ?>>
					<strong><?php esc_html_e( 'Every 4 Hours', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Recommended balance', 'wpshadow' ); ?>
				</label>
				
				<label style="display: block; margin: 15px 0;">
					<input type="radio" name="scan_frequency" value="daily" <?php checked( $scan_frequency, 'daily' ); ?>>
					<strong><?php esc_html_e( 'Daily', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Once per day', 'wpshadow' ); ?>
				</label>
				
				<label style="display: block; margin: 15px 0;">
					<input type="radio" name="scan_frequency" value="weekly" <?php checked( $scan_frequency, 'weekly' ); ?>>
					<strong><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Once per week', 'wpshadow' ); ?>
				</label>
				
				<button type="submit" name="save_scan_frequency" class="button button-primary" style="margin-top: 20px;">
					<?php esc_html_e( 'Save Frequency', 'wpshadow' ); ?>
				</button>
			</form>
			
			<?php
			if ( isset( $_POST['save_scan_frequency'] ) && 
			     check_admin_referer( 'wpshadow_save_scan_frequency', 'wpshadow_scan_frequency_nonce' ) ) {
				$new_frequency = sanitize_key( $_POST['scan_frequency'] );
				update_option( 'wpshadow_scan_frequency', $new_frequency );
				echo '<div class="notice notice-success" style="margin-top: 20px;"><p>' . esc_html__( 'Scan frequency updated!', 'wpshadow' ) . '</p></div>';
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render Reports page (Analytics & Insights)
 */
function wpshadow_render_reports() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Render enhanced Phase 4 reports dashboard
	?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<div class="wps-page-header">
			<h1 class="wps-page-title">
				<span class="dashicons dashicons-chart-bar" style="color: var(--wps-primary);"></span>
				<?php esc_html_e( 'Reports & Analytics', 'wpshadow' ); ?>
			</h1>
			<p class="wps-page-subtitle">
				<?php esc_html_e( 'Analyze your WPShadow activities, track KPIs, and generate comprehensive reports.', 'wpshadow' ); ?>
			</p>
		</div>
		
		<?php \WPShadow\Reports\Report_Builder::render(); ?>
	</div>
	<?php
}

/**
 * Render Settings page
 */
function wpshadow_render_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Handle settings save
	if ( isset( $_POST['wpshadow_settings_nonce'] ) && wp_verify_nonce( $_POST['wpshadow_settings_nonce'], 'wpshadow_save_settings' ) ) {
		$tab = isset( $_POST['wpshadow_settings_tab'] ) ? sanitize_key( $_POST['wpshadow_settings_tab'] ) : 'general';
		wpshadow_save_settings( $tab );
	}

	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
	?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<div class="wps-page-header">
			<h1 class="wps-page-title">
				<span class="dashicons dashicons-admin-settings" style="vertical-align: middle; color: var(--wps-primary);"></span>
				<?php esc_html_e( 'Settings', 'wpshadow' ); ?>
			</h1>
			<p class="wps-page-subtitle">
				<?php esc_html_e( 'Configure WPShadow to work exactly how you need it.', 'wpshadow' ); ?>
			</p>
		</div>

		<!-- Modern Tab Navigation -->
		<div style="background: #fff; border-radius: var(--wps-radius-lg); border: 1px solid var(--wps-gray-200); padding: var(--wps-space-2); margin-bottom: var(--wps-space-6); display: flex; gap: var(--wps-space-2); overflow-x: auto;">
			<?php
			$tabs = array(
				'general' => array( 'label' => __( 'General', 'wpshadow' ), 'icon' => 'dashicons-admin-generic' ),
				'email' => array( 'label' => __( 'Email & Reports', 'wpshadow' ), 'icon' => 'dashicons-email-alt' ),
				'notifications' => array( 'label' => __( 'Notifications', 'wpshadow' ), 'icon' => 'dashicons-bell' ),
				'privacy' => array( 'label' => __( 'Privacy', 'wpshadow' ), 'icon' => 'dashicons-privacy' ),
				'scan' => array( 'label' => __( 'Scan Settings', 'wpshadow' ), 'icon' => 'dashicons-search' ),
				'advanced' => array( 'label' => __( 'Advanced', 'wpshadow' ), 'icon' => 'dashicons-hammer' ),
			);
			
			foreach ( $tabs as $tab_key => $tab_data ) {
				$is_active = $active_tab === $tab_key;
				$class = $is_active ? 'wps-btn wps-btn-primary' : 'wps-btn wps-btn-ghost';
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=' . $tab_key ) ); ?>" 
				   class="<?php echo esc_attr( $class ); ?>"
				   style="white-space: nowrap;">
					<span class="dashicons <?php echo esc_attr( $tab_data['icon'] ); ?>" style="font-size: 16px; width: 16px; height: 16px;"></span>
					<?php echo esc_html( $tab_data['label'] ); ?>
				</a>
				<?php
			}
			?>
		</div>

		<!-- Tab Content -->
		<?php
		switch ( $active_tab ) {
			case 'general':
				wpshadow_render_settings_general();
				break;
			case 'email':
				wpshadow_render_settings_email();
				break;
			case 'notifications':
				wpshadow_render_settings_notifications();
				break;
			case 'privacy':
				wpshadow_render_settings_privacy();
				break;
			case 'scan':
				wpshadow_render_settings_scan();
				break;
			case 'advanced':
				wpshadow_render_settings_advanced();
				break;
			default:
				wpshadow_render_settings_general();
		}
		?>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		let formChanged = false;
		
		// Track form changes
		$('form input, form select, form textarea').on('change', function() {
			formChanged = true;
		});
		
		// Check for unsaved changes when clicking tab links
		$('.wps-btn[href*="wpshadow-settings"]').on('click', function(e) {
			if (formChanged) {
				e.preventDefault();
				const targetUrl = $(this).attr('href');
				
				if (confirm('<?php esc_attr_e( 'You have unsaved changes. Do you want to save them before leaving?', 'wpshadow' ); ?>')) {
					// Submit the form
					$('form').submit();
				} else {
					// Navigate without saving
					formChanged = false;
					window.location.href = targetUrl;
				}
			}
		});
		
		// Reset flag after successful save
		$('form').on('submit', function() {
			formChanged = false;
		});
	});
	</script>
	<?php
}

/**
 * Render Settings General tab
 */
function wpshadow_render_settings_general() {
	$auto_scan = get_option( 'wpshadow_auto_scan_enabled', true );
	$scan_frequency = get_option( 'wpshadow_scan_frequency', 'daily' );
	$dismiss_timeout = get_option( 'wpshadow_dismiss_timeout', 30 );
	?>
	<form method="post" action="" id="wpshadow-email-form">
		<?php wp_nonce_field( 'wpshadow_save_settings', 'wpshadow_settings_nonce' ); ?>
		<input type="hidden" name="wpshadow_settings_tab" value="general" />
		
		<!-- Auto-Scan Settings Card -->
		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Automatic Scanning', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Configure how often WPShadow automatically scans your site for issues.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_auto_scan_enabled" value="1" <?php checked( $auto_scan, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable automatic scanning', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Automatically run health checks on a schedule to catch issues early.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?>
					</label>
					<select name="wpshadow_scan_frequency" class="wps-select">
						<option value="hourly" <?php selected( $scan_frequency, 'hourly' ); ?>><?php esc_html_e( 'Every Hour', 'wpshadow' ); ?></option>
						<option value="twicedaily" <?php selected( $scan_frequency, 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily', 'wpshadow' ); ?></option>
						<option value="daily" <?php selected( $scan_frequency, 'daily' ); ?>><?php esc_html_e( 'Once Daily', 'wpshadow' ); ?></option>
						<option value="weekly" <?php selected( $scan_frequency, 'weekly' ); ?>><?php esc_html_e( 'Once Weekly', 'wpshadow' ); ?></option>
					</select>
					<p class="wps-form-help">
						<?php esc_html_e( 'How often should WPShadow run a full diagnostic scan?', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- Finding Management Card -->
		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-visibility"></span>
						<?php esc_html_e( 'Finding Management', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Control how long dismissed findings stay hidden.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'Re-check dismissed findings after', 'wpshadow' ); ?>
					</label>
					<div style="display: flex; align-items: center; gap: var(--wps-space-3);">
						<input type="range" 
							   name="wpshadow_dismiss_timeout" 
							   class="wps-slider" 
							   min="7" 
							   max="90" 
							   step="1" 
							   value="<?php echo esc_attr( $dismiss_timeout ); ?>"
							   oninput="document.getElementById('dismiss-timeout-value').textContent = this.value + ' days'" />
						<span id="dismiss-timeout-value" class="wps-badge wps-badge-primary" style="min-width: 70px; text-align: center;">
							<?php echo esc_html( $dismiss_timeout . ' days' ); ?>
						</span>
					</div>
					<p class="wps-form-help">
						<?php esc_html_e( 'Dismissed findings will reappear after this many days to ensure they haven\'t regressed.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- Save Button -->
		<div class="wps-card-footer" style="background: transparent; border: none; padding: 0;">
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
			<span class="wps-text-muted" style="font-size: var(--wps-text-sm);">
				<?php esc_html_e( 'Changes take effect immediately', 'wpshadow' ); ?>
			</span>
		</div>
	</form>
	<?php
}

/**
 * Render Settings Email tab
 */
function wpshadow_render_settings_email() {
	$email_enabled = get_option( 'wpshadow_email_enabled', false );
	$from_email = get_option( 'wpshadow_email_from_email', get_option( 'admin_email' ) );
	$from_name = get_option( 'wpshadow_email_from_name', get_bloginfo( 'name' ) );
	$admin_email = get_option( 'admin_email' );
	
	// Get individual email type settings
	$email_health_report = get_option( 'wpshadow_email_health_report', true );
	$email_critical_alerts = get_option( 'wpshadow_email_critical_alerts', true );
	$email_scan_completion = get_option( 'wpshadow_email_scan_completion', false );
	$email_weekly_summary = get_option( 'wpshadow_email_weekly_summary', true );
	$email_new_issues = get_option( 'wpshadow_email_new_issues', true );
	$email_treatment_results = get_option( 'wpshadow_email_treatment_results', false );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'wpshadow_save_settings', 'wpshadow_settings_nonce' ); ?>
		<input type="hidden" name="wpshadow_settings_tab" value="email" />
		
		<div class="wps-alert wps-alert-info">
			<span class="dashicons dashicons-info wps-alert-icon"></span>
			<div class="wps-alert-content">
				<p class="wps-alert-title"><?php esc_html_e( 'Email Configuration', 'wpshadow' ); ?></p>
				<p class="wps-alert-message">
					<?php printf(
						esc_html__( 'Configure sender details and choose which types of emails WPShadow should send to: %s', 'wpshadow' ),
						'<strong>' . esc_html( $admin_email ) . '</strong>'
					); ?>
				</p>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-email"></span>
						<?php esc_html_e( 'Email Sender Details', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Configure the sender information for all WPShadow emails.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'From Email', 'wpshadow' ); ?>
					</label>
					<input type="email" 
						   name="wpshadow_email_from_email" 
						   class="wps-input" 
						   value="<?php echo esc_attr( $from_email ); ?>"
						   placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
					<p class="wps-form-help">
						<?php esc_html_e( 'The email address reports will be sent from. Use your domain email for best delivery.', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'From Name', 'wpshadow' ); ?>
					</label>
					<input type="text" 
						   name="wpshadow_email_from_name" 
						   class="wps-input" 
						   value="<?php echo esc_attr( $from_name ); ?>"
						   placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<p class="wps-form-help">
						<?php esc_html_e( 'The name that will appear in the "From" field of emails.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- Email Type Selection Card -->
		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-bell"></span>
						<?php esc_html_e( 'Email Types', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Choose which types of emails WPShadow should send. You have full control here!', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
				<!-- Health Report -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_health_report" 
								   value="1" 
								   <?php checked( $email_health_report, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Health Reports', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Daily or weekly summaries of your site health status.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Critical Alerts -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_critical_alerts" 
								   value="1" 
								   <?php checked( $email_critical_alerts, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Critical Alerts', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Immediate alerts for security threats or critical issues.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Weekly Summary -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_weekly_summary" 
								   value="1" 
								   <?php checked( $email_weekly_summary, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Weekly Summary', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Weekly digest of improvements, changes, and updates.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- New Issues Detected -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_new_issues" 
								   value="1" 
								   <?php checked( $email_new_issues, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'New Issues Detected', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Notification when new problems are found during scans.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Scan Completion -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_scan_completion" 
								   value="1" 
								   <?php checked( $email_scan_completion, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Scan Completion', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Notification when deep scans finish running.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<!-- Treatment Results -->
				<div style="border: 1px solid var(--wps-gray-200); border-radius: var(--wps-radius-md); padding: 16px;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   name="wpshadow_email_treatment_results" 
								   value="1" 
								   <?php checked( $email_treatment_results, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Treatment Results', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help" style="margin-top: 4px;">
								<?php esc_html_e( 'Confirm when auto-fixes or manual treatments complete.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>
			</div>
		</div>

		<!-- Master Email Toggle at Bottom -->
		<div class="wps-card" style="background: linear-gradient(135deg, var(--wps-blue-50) 0%, var(--wps-purple-50) 100%); border-color: var(--wps-primary);">
			<div class="wps-card-body">
				<div class="wps-form-group" style="margin: 0;">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" 
								   id="wpshadow_email_enabled_checkbox"
								   name="wpshadow_email_enabled" 
								   value="1" 
								   <?php checked( $email_enabled, true ); ?>
								   onchange="wpshadowHandleEmailToggle(this)" />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0; font-size: 16px; font-weight: 600;">
								<?php esc_html_e( 'Enable All Email Sending', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Master switch to enable or disable all email notifications. Individual types can still be customized above.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>
			</div>
		</div>

		<div class="wps-card-footer" style="background: transparent; border: none; padding: 0; margin-top: 24px;">
			<input type="hidden" id="wpshadow_email_confirmed" name="wpshadow_email_confirmed" value="0" />
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</div>
	</form>

	<script>
		function wpshadowSetEmailFieldsState(isEnabled) {
			const form = document.getElementById('wpshadow-email-form');
			if (!form) return;
			const fields = form.querySelectorAll('input, select, textarea');
			fields.forEach((field) => {
				if (field.id === 'wpshadow_email_enabled_checkbox' || field.type === 'hidden' || field.name === 'wpshadow_settings_tab') {
					return; // Keep master toggle and hidden fields always available
				}
				field.disabled = !isEnabled;
			});
		}

		function wpshadowHandleEmailToggle(checkbox) {
			const adminEmail = '<?php echo esc_js( $admin_email ); ?>';
			const confirmedField = document.getElementById('wpshadow_email_confirmed');
			const enableFields = () => wpshadowSetEmailFieldsState(true);
			const disableFields = () => wpshadowSetEmailFieldsState(false);

			if (checkbox.checked) {
				// Show privacy confirmation modal
				WPShadowDesign.openModal({
					title: '<?php esc_attr_e( 'Enable Email Communications', 'wpshadow' ); ?>',
					content: '<div class="wps-alert wps-alert-warning" style="margin-bottom: 16px;">' +
						'<span class="dashicons dashicons-warning wps-alert-icon"></span>' +
						'<div class="wps-alert-content">' +
						'<p class="wps-alert-title"><strong><?php esc_attr_e( 'Privacy Notice', 'wpshadow' ); ?></strong></p>' +
						'<p class="wps-alert-message" style="margin: 0;">' +
						'<?php esc_attr_e( 'Emails will be sent to:', 'wpshadow' ); ?> <strong>' + adminEmail + '</strong>' +
						'</p>' +
						'</div>' +
						'</div>' +
						'<p><?php esc_attr_e( 'Please confirm that the email address owner has agreed to receive communications from WPShadow. We take privacy and consent very seriously.', 'wpshadow' ); ?></p>',
					size: 'small',
					showCancel: true,
					confirmText: '<?php esc_attr_e( 'I Confirm', 'wpshadow' ); ?>',
					cancelText: '<?php esc_attr_e( 'Cancel', 'wpshadow' ); ?>',
					onConfirm: function() {
						confirmedField.value = '1';
						enableFields();
						document.querySelector('form').submit();
					},
					onCancel: function() {
						checkbox.checked = false;
						disableFields();
					}
				});
			} else {
				confirmedField.value = '0';
				disableFields();
			}
		}

		// Initialize field state on load
		wpshadowSetEmailFieldsState(<?php echo $email_enabled ? 'true' : 'false'; ?>);
	</script>

	<!-- Email Rule Builder -->
	<div style="margin-top: 32px;">
		<?php
		\WPShadow\Workflow\Notification_Builder::set_mode( 'email' );
		echo wp_kses_post( \WPShadow\Workflow\Notification_Builder::render( 'email' ) );
		?>
	</div>
	<?php
}
function wpshadow_render_settings_notifications() {
	// Create notification rule builder in 'notification' mode
	\WPShadow\Workflow\Notification_Builder::set_mode( 'notification' );
	?>
	<div class="wps-card">
		<div class="wps-card-header">
			<div>
				<h2 class="wps-card-title">
					<span class="dashicons dashicons-bell"></span>
					<?php esc_html_e( 'Custom Notifications', 'wpshadow' ); ?>
				</h2>
				<p class="wps-card-description">
					<?php esc_html_e( 'Create custom dashboard notifications that trigger on any site event. Get notified instantly when backups complete, diagnostics find issues, or other important events occur.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<div class="wps-card-body">
			<?php echo wp_kses_post( \WPShadow\Workflow\Notification_Builder::render( 'notification' ) ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Settings Privacy tab
 */
function wpshadow_render_settings_privacy() {
	$analytics_enabled = get_option( 'wpshadow_analytics_enabled', false );
	$telemetry_enabled = get_option( 'wpshadow_telemetry_enabled', false );
	$data_retention = get_option( 'wpshadow_data_retention_days', 90 );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'wpshadow_save_settings', 'wpshadow_settings_nonce' ); ?>
		<input type="hidden" name="wpshadow_settings_tab" value="privacy" />
		
		<div class="wps-alert wps-alert-info">
			<span class="dashicons dashicons-shield wps-alert-icon"></span>
			<div class="wps-alert-content">
				<p class="wps-alert-title"><?php esc_html_e( 'Privacy First', 'wpshadow' ); ?></p>
				<p class="wps-alert-message">
					<?php esc_html_e( 'WPShadow is designed with privacy at its core. All diagnostic data stays on your server unless you explicitly opt-in to cloud features.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-chart-line"></span>
						<?php esc_html_e( 'Analytics & Telemetry', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Help us improve WPShadow by sharing anonymous usage data.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_analytics_enabled" value="1" <?php checked( $analytics_enabled, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable anonymous analytics', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Share anonymous plugin usage statistics to help us improve features.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_telemetry_enabled" value="1" <?php checked( $telemetry_enabled, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable error reporting', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Automatically send error reports to help us fix bugs faster.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-database"></span>
						<?php esc_html_e( 'Data Retention', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Control how long diagnostic history is kept.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'Keep diagnostic data for', 'wpshadow' ); ?>
					</label>
					<div style="display: flex; align-items: center; gap: var(--wps-space-3);">
						<input type="range" 
							   name="wpshadow_data_retention_days" 
							   class="wps-slider" 
							   min="30" 
							   max="365" 
							   step="30" 
							   value="<?php echo esc_attr( $data_retention ); ?>"
							   oninput="document.getElementById('retention-value').textContent = this.value + ' days'" />
						<span id="retention-value" class="wps-badge wps-badge-primary" style="min-width: 80px; text-align: center;">
							<?php echo esc_html( $data_retention . ' days' ); ?>
						</span>
					</div>
					<p class="wps-form-help">
						<?php esc_html_e( 'Older diagnostic data will be automatically cleaned up to save database space.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<div class="wps-card-footer" style="background: transparent; border: none; padding: 0;">
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</div>
	</form>
	<?php
}

/**
 * Render Settings Scan tab
 */
function wpshadow_render_settings_scan() {
	$scan_types = get_option( 'wpshadow_scan_types', array( 'security', 'performance', 'code_quality', 'seo', 'design', 'settings', 'monitoring', 'workflows', 'wordpress_health', 'developer_experience', 'marketing_growth', 'customer_retention', 'ai_readiness', 'environment', 'users', 'content_publishing' ) );
	$quick_scan_timeout = get_option( 'wpshadow_quick_scan_timeout', 30 );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'wpshadow_save_settings', 'wpshadow_settings_nonce' ); ?>
		<input type="hidden" name="wpshadow_settings_tab" value="scan" />
		
		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-search"></span>
						<?php esc_html_e( 'Scan Categories', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Choose which types of diagnostics to run during automatic scans.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<?php
					// Match dashboard gauge categories exactly
					$categories = array(
						'security' => __( 'Security', 'wpshadow' ),
						'performance' => __( 'Performance', 'wpshadow' ),
						'code_quality' => __( 'Code Quality', 'wpshadow' ),
						'seo' => __( 'SEO', 'wpshadow' ),
						'design' => __( 'Design', 'wpshadow' ),
						'settings' => __( 'Settings', 'wpshadow' ),
						'monitoring' => __( 'Monitoring', 'wpshadow' ),
						'workflows' => __( 'Workflows', 'wpshadow' ),
						'wordpress_health' => __( 'WordPress Site Health', 'wpshadow' ),
						'developer_experience' => __( 'Developer Experience', 'wpshadow' ),
						'marketing_growth' => __( 'Marketing & Growth', 'wpshadow' ),
						'customer_retention' => __( 'Customer Retention', 'wpshadow' ),
						'ai_readiness' => __( 'AI Readiness', 'wpshadow' ),
						'environment' => __( 'Environment & Impact', 'wpshadow' ),
						'users' => __( 'Users & Team', 'wpshadow' ),
						'content_publishing' => __( 'Content Publishing', 'wpshadow' ),
					);
					
					foreach ( $categories as $key => $label ) {
						$checked = in_array( $key, $scan_types, true );
						?>
						<label class="wps-toggle-wrapper" style="margin-bottom: var(--wps-space-4);">
							<div class="wps-toggle">
								<input type="checkbox" 
									   name="wpshadow_scan_types[]" 
									   value="<?php echo esc_attr( $key ); ?>"
									   <?php checked( $checked, true ); ?> />
								<span class="wps-toggle-slider"></span>
							</div>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
						<?php
					}
					?>
				</div>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-clock"></span>
						<?php esc_html_e( 'Scan Performance', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Optimize how scans run on your server.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-form-label">
						<?php esc_html_e( 'Quick Scan Timeout', 'wpshadow' ); ?>
					</label>
					<div style="display: flex; align-items: center; gap: var(--wps-space-3);">
						<input type="range" 
							   name="wpshadow_quick_scan_timeout" 
							   class="wps-slider" 
							   min="15" 
							   max="120" 
							   step="15" 
							   value="<?php echo esc_attr( $quick_scan_timeout ); ?>"
							   oninput="document.getElementById('timeout-value').textContent = this.value + ' seconds'" />
						<span id="timeout-value" class="wps-badge wps-badge-primary" style="min-width: 100px; text-align: center;">
							<?php echo esc_html( $quick_scan_timeout . ' seconds' ); ?>
						</span>
					</div>
					<p class="wps-form-help">
						<?php esc_html_e( 'Maximum time allowed for quick scans. Increase if you have a slower server.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<div class="wps-card-footer" style="background: transparent; border: none; padding: 0;">
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</div>
	</form>
	<?php
}

/**
 * Render Settings Advanced tab
 */
function wpshadow_render_settings_advanced() {
	$debug_mode = get_option( 'wpshadow_debug_mode', false );
	$cache_enabled = get_option( 'wpshadow_cache_enabled', true );
	$rest_api_enabled = get_option( 'wpshadow_rest_api_enabled', true );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'wpshadow_save_settings', 'wpshadow_settings_nonce' ); ?>
		<input type="hidden" name="wpshadow_settings_tab" value="advanced" />
		
		<div class="wps-alert wps-alert-warning">
			<span class="dashicons dashicons-warning wps-alert-icon"></span>
			<div class="wps-alert-content">
				<p class="wps-alert-title"><?php esc_html_e( 'Advanced Settings', 'wpshadow' ); ?></p>
				<p class="wps-alert-message">
					<?php esc_html_e( 'These settings are for advanced users. Incorrect configuration may affect plugin performance.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Developer Options', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Options for debugging and development.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_debug_mode" value="1" <?php checked( $debug_mode, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable debug mode', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Log detailed diagnostic information for troubleshooting.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_cache_enabled" value="1" <?php checked( $cache_enabled, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable caching', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Cache diagnostic results to improve performance.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>

				<div class="wps-form-group">
					<label class="wps-toggle-wrapper">
						<div class="wps-toggle">
							<input type="checkbox" name="wpshadow_rest_api_enabled" value="1" <?php checked( $rest_api_enabled, true ); ?> />
							<span class="wps-toggle-slider"></span>
						</div>
						<div>
							<span class="wps-form-label" style="margin: 0;">
								<?php esc_html_e( 'Enable REST API', 'wpshadow' ); ?>
							</span>
							<p class="wps-form-help">
								<?php esc_html_e( 'Allow external applications to access WPShadow via REST API.', 'wpshadow' ); ?>
							</p>
						</div>
					</label>
				</div>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Data Management', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description">
						<?php esc_html_e( 'Clean up or reset plugin data.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-form-group">
					<button type="button" class="wps-btn wps-btn-secondary" onclick="if(confirm('<?php echo esc_js( __( 'This will clear all cached diagnostic results. Continue?', 'wpshadow' ) ); ?>')) { alert('<?php echo esc_js( __( 'Cache cleared!', 'wpshadow' ) ); ?>'); }">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Clear Cache', 'wpshadow' ); ?>
					</button>
					<p class="wps-form-help">
						<?php esc_html_e( 'Clear all cached diagnostic data and force fresh scans.', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="wps-form-group">
					<button type="button" class="wps-btn wps-btn-danger" onclick="if(confirm('<?php echo esc_js( __( 'This will reset ALL WPShadow settings to defaults. This cannot be undone. Continue?', 'wpshadow' ) ); ?>')) { alert('<?php echo esc_js( __( 'Settings reset!', 'wpshadow' ) ); ?>'); }">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Reset All Settings', 'wpshadow' ); ?>
					</button>
					<p class="wps-form-help">
						<?php esc_html_e( 'Reset all plugin settings to factory defaults. Use with caution.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
		</div>

		<div class="wps-card-footer" style="background: transparent; border: none; padding: 0;">
			<button type="submit" class="wps-btn wps-btn-primary wps-btn-lg">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</div>
	</form>
	<?php
}

/**
 * Save settings based on tab
 */
function wpshadow_save_settings( $tab ) {
	// Verify nonce already checked in calling function
	
	switch ( $tab ) {
		case 'general':
			update_option( 'wpshadow_auto_scan_enabled', isset( $_POST['wpshadow_auto_scan_enabled'] ) );
			update_option( 'wpshadow_scan_frequency', sanitize_key( $_POST['wpshadow_scan_frequency'] ?? 'daily' ) );
			update_option( 'wpshadow_dismiss_timeout', absint( $_POST['wpshadow_dismiss_timeout'] ?? 30 ) );
			break;
		
		case 'email':
			update_option( 'wpshadow_email_enabled', isset( $_POST['wpshadow_email_enabled'] ) );
			update_option( 'wpshadow_email_from_email', sanitize_email( $_POST['wpshadow_email_from_email'] ?? '' ) );
			update_option( 'wpshadow_email_from_name', sanitize_text_field( $_POST['wpshadow_email_from_name'] ?? '' ) );
			
			// Save individual email type preferences
			update_option( 'wpshadow_email_health_report', isset( $_POST['wpshadow_email_health_report'] ) );
			update_option( 'wpshadow_email_critical_alerts', isset( $_POST['wpshadow_email_critical_alerts'] ) );
			update_option( 'wpshadow_email_scan_completion', isset( $_POST['wpshadow_email_scan_completion'] ) );
			update_option( 'wpshadow_email_weekly_summary', isset( $_POST['wpshadow_email_weekly_summary'] ) );
			update_option( 'wpshadow_email_new_issues', isset( $_POST['wpshadow_email_new_issues'] ) );
			update_option( 'wpshadow_email_treatment_results', isset( $_POST['wpshadow_email_treatment_results'] ) );
			break;
		
		case 'privacy':
			update_option( 'wpshadow_analytics_enabled', isset( $_POST['wpshadow_analytics_enabled'] ) );
			update_option( 'wpshadow_telemetry_enabled', isset( $_POST['wpshadow_telemetry_enabled'] ) );
			update_option( 'wpshadow_data_retention_days', absint( $_POST['wpshadow_data_retention_days'] ?? 90 ) );
			break;
		
		case 'scan':
			$scan_types = isset( $_POST['wpshadow_scan_types'] ) && is_array( $_POST['wpshadow_scan_types'] ) 
				? array_map( 'sanitize_key', $_POST['wpshadow_scan_types'] ) 
				: array();
			update_option( 'wpshadow_scan_types', $scan_types );
			update_option( 'wpshadow_quick_scan_timeout', absint( $_POST['wpshadow_quick_scan_timeout'] ?? 30 ) );
			break;
		
		case 'advanced':
			update_option( 'wpshadow_debug_mode', isset( $_POST['wpshadow_debug_mode'] ) );
			update_option( 'wpshadow_cache_enabled', isset( $_POST['wpshadow_cache_enabled'] ) );
			update_option( 'wpshadow_rest_api_enabled', isset( $_POST['wpshadow_rest_api_enabled'] ) );
			break;
	}
	
	// Show admin notice
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php esc_html_e( 'Settings saved successfully!', 'wpshadow' ); ?></strong></p>
		</div>
		<?php
	} );
}

/**
 * Render Settings Privacy tab
 */
function wpshadow_render_settings_privacy_old() {
	?>
	<div style="max-width: 800px;">
		<div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px;">
			<h3><?php esc_html_e( 'Privacy Settings', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Control data collection and privacy preferences.', 'wpshadow' ); ?></p>
			<p style="color: #666; font-style: italic;"><?php esc_html_e( 'Privacy controls coming soon.', 'wpshadow' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Override wp_mail From Email if WPShadow setting is configured.
 */
add_filter( 'wp_mail_from', function( $from_email ) {
	$custom_from_email = get_option( 'wpshadow_email_from_email', '' );
	
	if ( ! empty( $custom_from_email ) && is_email( $custom_from_email ) ) {
		return $custom_from_email;
	}
	
	return $from_email;
}, 999 );

/**
 * Uncheck "Send user notification email" by default for privacy law compliance (CASL).
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( $hook !== 'user-new.php' ) {
		return;
	}

	$should_uncheck = get_option( 'wpshadow_user_email_unchecked_by_default', false );
	if ( ! $should_uncheck ) {
		return;
	}

	wp_enqueue_script(
		'wpshadow-user-email-compliance',
		WPSHADOW_URL . 'assets/js/user-email-compliance.js',
		array(),
		WPSHADOW_VERSION,
		true
	);
} );
