<?php
/**
 * WPShadow Kanban Board Module
 *
 * Handles all Kanban board functionality including:
 * - Drag-and-drop finding management
 * - Family-aware smart fixing
 * - Column-based workflow organization
 * - Asset loading and localization
 *
 * @package WPShadow
 * @subpackage Kanban
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Kanban AJAX handlers
 */
function wpshadow_register_kanban_handlers(): void {
	// Change finding status handler is required in wpshadow.php (line ~1347)
	// Just register it here
	\WPShadow\Admin\Ajax\Change_Finding_Status_Handler::register();

	// Get finding family information (Philosophy #9: Show Value)
	require_once plugin_dir_path( __FILE__ ) . '../admin/ajax/class-get-finding-family-handler.php';
	\WPShadow\Admin\Ajax\Get_Finding_Family_Handler::register();

	// Apply family-grouped fixes (Philosophy #9: Show Value)
	require_once plugin_dir_path( __FILE__ ) . '../admin/ajax/class-apply-family-fix-handler.php';
	\WPShadow\Admin\Ajax\Apply_Family_Fix_Handler::register();
}
add_action( 'plugins_loaded', 'wpshadow_register_kanban_handlers' );

/**
 * Enqueue Kanban board assets
 */
function wpshadow_enqueue_kanban_assets( string $hook ): void {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Kanban board styles
	wp_enqueue_style(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/css/kanban-board.css',
		array(),
		WPSHADOW_VERSION
	);

	// Kanban board scripts
	wp_enqueue_script(
		'wpshadow-kanban-board',
		WPSHADOW_URL . 'assets/js/kanban-board.js',
		array( 'jquery' ),
		WPSHADOW_VERSION,
		true
	);

	// Localize script with nonce for AJAX
	wp_localize_script(
		'wpshadow-kanban-board',
		'wpshadowKanban',
		array(
			'kanban_nonce' => wp_create_nonce( 'wpshadow_kanban' ),
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_kanban_assets' );

/**
 * Render Kanban board on dashboard
 *
 * Displays the Kanban board view for organizing findings
 * into columns based on status and user actions
 */
function wpshadow_render_kanban_board(): void {
	// Pass category filter to Kanban board if present
	if ( ! empty( $_GET['kanban_category'] ) ) {
		$_GET['kanban_category'] = sanitize_key( wp_unslash( $_GET['kanban_category'] ) );
	}

	include WPSHADOW_PATH . 'includes/views/kanban-board.php';
}

/**
 * Hook Kanban board rendering into dashboard
 *
 * Renders after the main dashboard content
 */
add_action( 'wpshadow_dashboard_after_content', 'wpshadow_render_kanban_board' );
