<?php
/**
 * Menu Callback Stubs
 *
 * Temporary stub functions for menu callbacks that don't have dedicated files yet.
 * These prevent fatal errors when menus are registered but the actual page isn't loaded.
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_render_action_items' ) ) {
	/**
	 * Render Action Items page (Kanban Board)
	 */
	function wpshadow_render_action_items() {
		// Load the kanban board view
		if ( file_exists( WPSHADOW_PATH . 'includes/views/kanban-board.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/views/kanban-board.php';
		} else {
			echo '<div class="wrap"><h1>Action Items</h1><p>Loading action items...</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_guardian' ) ) {
	/**
	 * Render Guardian page (Diagnostics & Treatments)
	 */
	function wpshadow_render_guardian() {
		// Load Guardian Dashboard class if not already loaded
		if ( ! class_exists( '\WPShadow\Admin\Guardian_Dashboard' ) ) {
			require_once WPSHADOW_PATH . 'includes/admin/class-guardian-dashboard.php';
		}
		
		if ( class_exists( '\WPShadow\Admin\Guardian_Dashboard' ) ) {
			echo \WPShadow\Admin\Guardian_Dashboard::render();
		} else {
			echo '<div class="wrap"><h1>Guardian</h1><p>Diagnostics and treatments system.</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_reports' ) ) {
	/**
	 * Render Reports page
	 */
	function wpshadow_render_reports() {
		// Load Report Form class if not already loaded
		if ( ! class_exists( '\WPShadow\Admin\Report_Form' ) ) {
			require_once WPSHADOW_PATH . 'includes/screens/class-report-form.php';
		}
		
		if ( class_exists( '\WPShadow\Admin\Report_Form' ) ) {
			echo \WPShadow\Admin\Report_Form::render();
		} else {
			echo '<div class="wrap"><h1>Reports</h1><p>Site health reports and analytics.</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_settings' ) ) {
	/**
	 * Render Settings page
	 */
	function wpshadow_render_settings() {
		echo '<div class="wrap"><h1>Settings</h1><p>Plugin configuration settings.</p></div>';
	}
}

// Load Tools module (defines wpshadow_render_tools if not already defined)
if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-tools-page-module.php';
}

// Load Help module (defines wpshadow_render_help if not already defined)
if ( ! function_exists( 'wpshadow_render_help' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-help-page-module.php';
}

// Load Workflows module (defines wpshadow_render_workflow_builder if not already defined)
if ( ! function_exists( 'wpshadow_render_workflow_builder' ) ) {
	require_once WPSHADOW_PATH . 'includes/workflow/workflow-module.php';
}



if ( ! function_exists( 'wpshadow_render_visual_comparisons' ) ) {
	/**
	 * Render Visual Comparisons page
	 */
	function wpshadow_render_visual_comparisons() {
		echo '<div class="wrap"><h1>Visual Comparisons</h1><p>Visual regression testing coming soon.</p></div>';
	}
}

