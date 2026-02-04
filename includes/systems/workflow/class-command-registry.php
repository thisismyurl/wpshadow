<?php
/**
 * Workflow Command Registry
 *
 * Auto-registers all workflow AJAX commands.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load command base class
require_once WPSHADOW_PATH . '/includes/workflow/class-command.php';

// Load all command classes
$command_files = array(
	'commands/class-save-workflow-command.php',
	'commands/class-load-workflows-command.php',
	'commands/class-get-workflow-command.php',
	'commands/class-delete-workflow-command.php',
	'commands/class-toggle-workflow-command.php',
	'commands/class-run-workflow-command.php',
	'commands/class-get-available-actions-command.php',
	'commands/class-get-action-config-command.php',
	'commands/class-create-from-example-command.php',
	'commands/class-kb-search-command.php',
	'commands/class-register-cloud-command.php',
);

foreach ( $command_files as $file ) {
	$file_path = WPSHADOW_PATH . '/includes/workflow/' . $file;
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Register all commands
$command_classes = array(
	'WPShadow\\Workflow\\Commands\\Save_Workflow_Command',
	'WPShadow\\Workflow\\Commands\\Load_Workflows_Command',
	'WPShadow\\Workflow\\Commands\\Get_Workflow_Command',
	'WPShadow\\Workflow\\Commands\\Delete_Workflow_Command',
	'WPShadow\\Workflow\\Commands\\Toggle_Workflow_Command',
	'WPShadow\\Workflow\\Commands\\Run_Workflow_Command',
	'WPShadow\\Workflow\\Commands\\Get_Available_Actions_Command',
	'WPShadow\\Workflow\\Commands\\Get_Action_Config_Command',
	'WPShadow\\Workflow\\Commands\\Create_From_Example_Command',
	'WPShadow\\Workflow\\Commands\\KB_Search_Command',
	'WPShadow\\Workflow\\Commands\\Register_Cloud_Command',
);

foreach ( $command_classes as $class ) {
	if ( class_exists( $class ) ) {
		call_user_func( array( $class, 'register' ) );
	}
}
