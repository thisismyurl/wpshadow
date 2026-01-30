<?php

/**
 * WPShadow AJAX Router
 *
 * Centralizes registration of all AJAX handlers.
 * Extracted from wpshadow.php as part of Phase 4.5 refactoring.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - clear separation of concerns)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes and registers all AJAX handlers for WPShadow
 */
class AJAX_Router {


	/**
	 * Register all AJAX handlers
	 *
	 * Handlers are organized by functional area for easy discovery
	 *
	 * @return void
	 */
	public static function init() {
		// Debug logging
		$debug_file = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH . 'debug-ajax.log' : '/tmp/debug-ajax.log';
		file_put_contents( $debug_file, "AJAX_Router::init() called at " . date( 'Y-m-d H:i:s' ) . " - PHP_SAPI: " . PHP_SAPI . " - about to register Test_AJAX_Handler\n", FILE_APPEND );
		
		// Test handler (temporary)
		\WPShadow\Admin\Ajax\Test_AJAX_Handler::register();
		
		file_put_contents( $debug_file, "AJAX_Router::init() - Test_AJAX_Handler::register() completed\n", FILE_APPEND );
		
		// Core finding operations
		\WPShadow\Admin\Ajax\Dismiss_Finding_Handler::register();
		\WPShadow\Admin\Ajax\Autofix_Finding_Handler::register();
		\WPShadow\Admin\Ajax\Dry_Run_Treatment_Handler::register();
		\WPShadow\Admin\Ajax\Rollback_Treatment_Handler::register();
		\WPShadow\Admin\Ajax\Toggle_Autofix_Permission_Handler::register();
		\WPShadow\Admin\Ajax\Allow_All_Autofixes_Handler::register();
		\WPShadow\Admin\Ajax\Change_Finding_Status_Handler::register();

		// Dashboard operations
		\WPShadow\Admin\Ajax\Get_Dashboard_Data_Handler::register();
		\WPShadow\Admin\Ajax\Save_Dashboard_Prefs_Handler::register();

		// Scanning operations
		\WPShadow\Admin\Ajax\First_Scan_Handler::register();
		\WPShadow\Admin\Ajax\Quick_Scan_Handler::register();
		\WPShadow\Admin\Ajax\Deep_Scan_Handler::register();
		\WPShadow\Admin\Ajax\Dismiss_Scan_Notice_Handler::register();

		// Notifications and alerts
		\WPShadow\Admin\Ajax\Save_Tagline_Handler::register();
		\WPShadow\Admin\Ajax\Mark_Notification_Read_Handler::register();
		\WPShadow\Admin\Ajax\Clear_Notifications_Handler::register();

		// Gamification
		\WPShadow\Admin\Ajax\Get_Gamification_Summary_Handler::register();
		\WPShadow\Admin\Ajax\Get_Leaderboard_Handler::register();

		// Reporting
		\WPShadow\Admin\Ajax\Generate_Report_Handler::register();
		\WPShadow\Admin\Ajax\Download_Report_Handler::register();
		\WPShadow\Admin\Ajax\Send_Executive_Report_Handler::register();
		\WPShadow\Admin\Ajax\Export_CSV_Handler::register();

		// Settings management
		\WPShadow\Admin\Ajax\Save_Email_Template_Handler::register();
		\WPShadow\Admin\Ajax\Reset_Email_Template_Handler::register();
		\WPShadow\Admin\Ajax\Update_Report_Schedule_Handler::register();
		\WPShadow\Admin\Ajax\Update_Privacy_Settings_Handler::register();
		\WPShadow\Admin\Ajax\Update_Data_Retention_Handler::register();
		\WPShadow\Admin\Ajax\Update_Scan_Frequency_Handler::register();

		// Workflow operations
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
		\WPShadow\Admin\Ajax\Create_Suggested_Workflow_Handler::register();

		// Email recipient management (workflow notifications)
		\WPShadow\Admin\Ajax\Add_Email_Recipient_Handler::register();
		\WPShadow\Admin\Ajax\Approve_Email_Recipient_Handler::register();
		\WPShadow\Admin\Ajax\Remove_Email_Recipient_Handler::register();

		// Guardian operations
		\WPShadow\Admin\Ajax\Toggle_Guardian_Handler::register();

		// Off-peak scheduling
		\WPShadow\Admin\Ajax\Schedule_Overnight_Fix_Handler::register();
		\WPShadow\Admin\Ajax\Schedule_Offpeak_Handler::register();

		// Utilities
		\WPShadow\Admin\Ajax\Clear_Cache_Handler::register();
		\WPShadow\Admin\Ajax\Create_Magic_Link_Handler::register();
		\WPShadow\Admin\Ajax\Revoke_Magic_Link_Handler::register();
		\WPShadow\Admin\Ajax\Save_Cache_Options_Handler::register();
		\WPShadow\Admin\Ajax\Mobile_Check_Handler::register();
		\WPShadow\Admin\Ajax\A11y_Audit_Handler::register();
		\WPShadow\Admin\Ajax\Save_Tip_Prefs_Handler::register();
		\WPShadow\Admin\Ajax\Dismiss_Tip_Handler::register();
		\WPShadow\Admin\Ajax\Check_Broken_Links_Handler::register();
		\WPShadow\Admin\Ajax\Color_Contrast_Handler::register();
		\WPShadow\Admin\Ajax\Generate_Password_Handler::register();
		\WPShadow\Admin\Ajax\Consent_Preferences_Handler::register();
		\WPShadow\Admin\Ajax\Error_Report_Handler::register();
		\WPShadow\Admin\Ajax\Save_Notification_Rule_Handler::register();
		\WPShadow\Admin\Ajax\Delete_Notification_Rule_Handler::register();

		// Onboarding operations
		\WPShadow\Admin\Ajax\Save_Onboarding_Handler::register();
		\WPShadow\Admin\Ajax\Skip_Onboarding_Handler::register();
		\WPShadow\Admin\Ajax\Dismiss_Term_Handler::register();
		\WPShadow\Admin\Ajax\Show_All_Features_Handler::register();
		\WPShadow\Admin\Ajax\Dismiss_Graduation_Handler::register();

		// Timezone management
		\WPShadow\Admin\Ajax\Detect_Timezone_Handler::register();
		\WPShadow\Admin\Ajax\Set_Timezone_Handler::register();

		// Visual comparison operations
		\WPShadow\Admin\Ajax\Get_Visual_Comparisons_Handler::register();
		\WPShadow\Admin\Ajax\Get_Visual_Comparison_Handler::register();

		// Utilities operations
		\WPShadow\Admin\Ajax\Load_Tool_Handler::register();

		// Exit interview operations
		\WPShadow\Admin\Ajax\Submit_Exit_Interview_Handler::register();
	}
}
