<?php
/**
 * Workflow AJAX Handlers (DEPRECATED)
 *
 * @package WPShadow
 * @subpackage Workflow
 *
 * NOTE: All AJAX handlers have been migrated to class-based handlers in Phase 3.5.1
 * See: includes/admin/ajax/class-*-workflow-*-handler.php
 *
 * These handlers now extend AJAX_Handler_Base for centralized security and error handling.
 * This file is retained for reference and can be safely removed.
 *
 * Migration Status:
 * ✅ Save_Workflow_Handler (handles both block and wizard formats)
 * ✅ Load_Workflows_Handler
 * ✅ Get_Workflow_Handler
 * ✅ Delete_Workflow_Handler
 * ✅ Toggle_Workflow_Handler
 * ✅ Generate_Workflow_Name_Handler
 * ✅ Get_Available_Actions_Handler
 * ✅ Get_Action_Config_Handler
 * ✅ Run_Workflow_Handler
 * ✅ Create_From_Example_Handler
 * ✅ Get_Examples_Handler (in Load_Workflows_Handler)
 *
 * Benefits of Migration:
 * - Centralized nonce verification via AJAX_Handler_Base::verify_request()
 * - Centralized parameter handling via AJAX_Handler_Base::get_post_param()
 * - Consistent error responses via AJAX_Handler_Base::send_error()
 * - Consistent success responses via AJAX_Handler_Base::send_success()
 * - Type-safe parameter handling
 * - Easier testing and maintenance
 * - ~120 lines of duplicate code eliminated
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All handlers now registered in wpshadow.php via plugins_loaded hook
// Old inline handlers commented out below for reference only

/*
// MIGRATION HISTORY - Old inline handlers (no longer active)

// Previous Save_Workflow Handler (MIGRATED to class-save-workflow-handler.php)
// add_action( 'wp_ajax_wpshadow_save_workflow', function() { ... } );

// Previous Load_Workflows Handler (MIGRATED to class-load-workflows-handler.php)
// add_action( 'wp_ajax_wpshadow_load_workflows', function() { ... } );

// Previous Get_Workflow Handler (MIGRATED to class-get-workflow-handler.php)
// add_action( 'wp_ajax_wpshadow_get_workflow', function() { ... } );

// Previous Delete_Workflow Handler (MIGRATED to class-delete-workflow-handler.php)
// add_action( 'wp_ajax_wpshadow_delete_workflow', function() { ... } );

// Previous Toggle_Workflow Handler (MIGRATED to class-toggle-workflow-handler.php)
// add_action( 'wp_ajax_wpshadow_toggle_workflow', function() { ... } );

// Previous Generate_Workflow_Name Handler (MIGRATED to class-generate-workflow-name-handler.php)
// add_action( 'wp_ajax_wpshadow_generate_workflow_name', function() { ... } );

// Previous Get_Available_Actions Handler (MIGRATED to class-get-available-actions-handler.php)
// add_action( 'wp_ajax_wpshadow_get_available_actions', function() { ... } );

// Previous Get_Action_Config Handler (MIGRATED to class-get-action-config-handler.php)
// add_action( 'wp_ajax_wpshadow_get_action_config', function() { ... } );

// Previous Run_Workflow Handler (MIGRATED to class-run-workflow-handler.php)
// add_action( 'wp_ajax_wpshadow_run_workflow', function() { ... } );

// Previous Create_From_Example Handler (MIGRATED to class-create-from-example-handler.php)
// add_action( 'wp_ajax_wpshadow_create_from_example', function() { ... } );

// Previous Get_Examples Handler (MIGRATED to class-load-workflows-handler.php)
// add_action( 'wp_ajax_wpshadow_get_examples', function() { ... } );

*/
