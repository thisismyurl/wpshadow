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
	 * This stub will be replaced when kanban-module.php is loaded
	 */
	function wpshadow_render_action_items() {
		echo '<div class="wrap"><h1>Action Items</h1><p>Loading action items...</p></div>';
	}
}

if ( ! function_exists( 'wpshadow_render_guardian' ) ) {
	/**
	 * Render Guardian page (Diagnostics & Treatments)
	 */
	function wpshadow_render_guardian() {
		echo '<div class="wrap"><h1>Guardian</h1><p>Diagnostics and treatments system.</p></div>';
	}
}

if ( ! function_exists( 'wpshadow_render_reports' ) ) {
	/**
	 * Render Reports page
	 */
	function wpshadow_render_reports() {
		echo '<div class="wrap"><h1>Reports</h1><p>Site health reports and analytics.</p></div>';
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

if ( ! function_exists( 'wpshadow_render_visual_comparisons' ) ) {
	/**
	 * Render Visual Comparisons page
	 */
	function wpshadow_render_visual_comparisons() {
		echo '<div class="wrap"><h1>Visual Comparisons</h1><p>Visual regression testing coming soon.</p></div>';
	}
}

