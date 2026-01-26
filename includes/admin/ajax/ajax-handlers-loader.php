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
require_once $ajax_path . 'Dismiss_Finding_Handler.php';
require_once $ajax_path . 'Autofix_Finding_Handler.php';
require_once $ajax_path . 'Dry_Run_Treatment_Handler.php';
require_once $ajax_path . 'Rollback_Treatment_Handler.php';
require_once $ajax_path . 'Toggle_Autofix_Permission_Handler.php';
require_once $ajax_path . 'Allow_All_Autofixes_Handler.php';
require_once $ajax_path . 'Change_Finding_Status_Handler.php';

// Dashboard operations
require_once $ajax_path . 'Get_Dashboard_Data_Handler.php';
require_once $ajax_path . 'Save_Dashboard_Prefs_Handler.php';

// Scanning operations
require_once $ajax_path . 'First_Scan_Handler.php';
require_once $ajax_path . 'Quick_Scan_Handler.php';
require_once $ajax_path . 'Deep_Scan_Handler.php';
require_once $ajax_path . 'Dismiss_Scan_Notice_Handler.php';

// Notifications and alerts
require_once $ajax_path . 'Save_Tagline_Handler.php';
require_once $ajax_path . 'Mark_Notification_Read_Handler.php';
require_once $ajax_path . 'Clear_Notifications_Handler.php';

// Gamification
require_once $ajax_path . 'Get_Gamification_Summary_Handler.php';
require_once $ajax_path . 'Get_Leaderboard_Handler.php';

// Reporting
require_once $ajax_path . 'Generate_Report_Handler.php';
require_once $ajax_path . 'Download_Report_Handler.php';
require_once $ajax_path . 'Send_Executive_Report_Handler.php';
require_once $ajax_path . 'Export_CSV_Handler.php';

// Settings management
require_once $ajax_path . 'Save_Email_Template_Handler.php';
require_once $ajax_path . 'Reset_Email_Template_Handler.php';
require_once $ajax_path . 'Update_Report_Schedule_Handler.php';
require_once $ajax_path . 'Update_Privacy_Settings_Handler.php';
require_once $ajax_path . 'Update_Data_Retention_Handler.php';
require_once $ajax_path . 'Update_Scan_Frequency_Handler.php';

// Workflow operations
require_once $ajax_path . 'Save_Workflow_Handler.php';
require_once $ajax_path . 'Load_Workflows_Handler.php';
require_once $ajax_path . 'Get_Workflow_Handler.php';
require_once $ajax_path . 'Delete_Workflow_Handler.php';
require_once $ajax_path . 'Toggle_Workflow_Handler.php';
require_once $ajax_path . 'Generate_Workflow_Name_Handler.php';
require_once $ajax_path . 'Get_Available_Actions_Handler.php';
require_once $ajax_path . 'Get_Action_Config_Handler.php';
require_once $ajax_path . 'Run_Workflow_Handler.php';
require_once $ajax_path . 'Create_From_Example_Handler.php';
require_once $ajax_path . 'Create_Suggested_Workflow_Handler.php';
require_once $ajax_path . 'Get_Templates_Handler.php';
require_once $ajax_path . 'Create_From_Template_Handler.php';

// Email recipient management
require_once $ajax_path . 'Add_Email_Recipient_Handler.php';
require_once $ajax_path . 'Approve_Email_Recipient_Handler.php';
require_once $ajax_path . 'Remove_Email_Recipient_Handler.php';

// Guardian operations
require_once $ajax_path . 'Toggle_Guardian_Handler.php';

// Off-peak scheduling
require_once $ajax_path . 'Schedule_Overnight_Fix_Handler.php';
require_once $ajax_path . 'Schedule_Offpeak_Handler.php';

// Utilities
require_once $ajax_path . 'Clear_Cache_Handler.php';
require_once $ajax_path . 'Create_Magic_Link_Handler.php';
require_once $ajax_path . 'Revoke_Magic_Link_Handler.php';
require_once $ajax_path . 'Save_Cache_Options_Handler.php';
require_once $ajax_path . 'Mobile_Check_Handler.php';
require_once $ajax_path . 'Save_Tip_Prefs_Handler.php';
require_once $ajax_path . 'Dismiss_Tip_Handler.php';
require_once $ajax_path . 'Check_Broken_Links_Handler.php';
require_once $ajax_path . 'Generate_Password_Handler.php';
require_once $ajax_path . 'Consent_Preferences_Handler.php';
require_once $ajax_path . 'Error_Report_Handler.php';
require_once $ajax_path . 'Save_Notification_Rule_Handler.php';
require_once $ajax_path . 'Delete_Notification_Rule_Handler.php';

// Onboarding operations
require_once $ajax_path . 'Save_Onboarding_Handler.php';
require_once $ajax_path . 'Skip_Onboarding_Handler.php';
require_once $ajax_path . 'Dismiss_Term_Handler.php';
require_once $ajax_path . 'Show_All_Features_Handler.php';
require_once $ajax_path . 'Dismiss_Graduation_Handler.php';

// Timezone management
require_once $ajax_path . 'Detect_Timezone_Handler.php';
require_once $ajax_path . 'Set_Timezone_Handler.php';

// Visual comparison operations
require_once $ajax_path . 'Get_Visual_Comparisons_Handler.php';
require_once $ajax_path . 'Get_Visual_Comparison_Handler.php';

// Exit interview operations
require_once $ajax_path . 'Submit_Exit_Interview_Handler.php';

// Kanban operations (loaded separately in kanban-module.php)
// - Get_Finding_Family_Handler.php
// - Apply_Family_Fix_Handler.php

// Exit interview and followup operations
require_once $ajax_path . 'exit-followup-handlers.php';

