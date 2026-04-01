<?php
/**
 * Workflow Module - Workflow Builder Page
 *
 * Provides the render function for the workflow builder interface.
 *
 * @package WPShadow
 * @subpackage Workflow
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load all workflow-related classes
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-manager.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-discovery.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-wizard.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-suggestions.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-kanban-workflow-helper.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-block-registry.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-executor.php';

// Load AJAX handlers
require_once WPSHADOW_PATH . 'includes/admin/ajax/save-workflow-handler.php';

// Use statement for cleaner code.
use WPShadow\Workflow\Block_Registry;

/**
 * Render the Workflow Builder page
 *
 * Normalizes copied/encoded query strings and routes to either the
 * automations wizard (create/edit) or the automations dashboard list view.
 * Also enqueues required dashboard assets and localized strings.
 *
 * @since 0.6093.1200
 * @return void
 */
function wpshadow_render_workflow_builder() {
	// Verify user capabilities.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Support URLs copied with HTML-encoded query separators.
	if ( isset( $_GET['page'] ) && is_string( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page_param = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 === strpos( $page_param, 'wpshadow-automations' ) && false !== strpos( $page_param, '&' ) ) {
			$decoded = html_entity_decode( $page_param, ENT_QUOTES, 'UTF-8' );
			$parts   = explode( '&', $decoded );
			array_shift( $parts );

			$extra_args = array();
			parse_str( implode( '&', $parts ), $extra_args );
			foreach ( array( 'action', 'step', 'trigger', 'workflow' ) as $key ) {
				if ( empty( $_GET[ $key ] ) && isset( $extra_args[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$_GET[ $key ] = sanitize_key( $extra_args[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}
		}
	}

	// Recover params when HTML entities were pasted into the query string.
	if ( isset( $_GET['page'] ) && is_string( $_GET['page'] ) && empty( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page_param = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 0 === strpos( $page_param, 'wpshadow-automations' ) && ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$query_string  = wp_unslash( $_SERVER['QUERY_STRING'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$decoded_query = html_entity_decode( rawurldecode( $query_string ), ENT_QUOTES, 'UTF-8' );
			$parsed_args   = array();
			parse_str( $decoded_query, $parsed_args );

			if ( empty( $_GET['action'] ) && ! empty( $parsed_args['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_GET['action'] = sanitize_key( $parsed_args['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			if ( empty( $_GET['step'] ) && ! empty( $parsed_args['step'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_GET['step'] = sanitize_key( $parsed_args['step'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			if ( empty( $_GET['trigger'] ) && ! empty( $parsed_args['trigger'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_GET['trigger'] = sanitize_key( $parsed_args['trigger'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			if ( empty( $_GET['workflow'] ) && ! empty( $parsed_args['workflow'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_GET['workflow'] = absint( $parsed_args['workflow'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}
	}

	// Get the current action.
	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Route to the wizard for create/edit actions (new simplified interface).
	if ( in_array( $action, array( 'create', 'edit' ), true ) ) {
		// Load the automations wizard
		require_once WPSHADOW_PATH . 'includes/ui/templates/automations-wizard.php';
		return;
	} else {
		// Enqueue modal system for detail modal.
		wp_enqueue_script(
			'wpshadow-modal',
			WPSHADOW_URL . 'assets/js/wpshadow-modal.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue dashboard assets.
		wp_enqueue_script(
			'wpshadow-automations-dashboard',
			WPSHADOW_URL . 'assets/js/automations-dashboard.js',
			array( 'jquery', 'wpshadow-modal' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with data.
		wp_localize_script(
			'wpshadow-automations-dashboard',
			'wpshadowAutomationsDashboard',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_automations' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'strings' => array(
					'confirmDelete'        => __( 'Are you sure you want to delete this automation? This action cannot be undone.', 'wpshadow' ),
					'deleteSuccess'        => __( 'Automation deleted successfully', 'wpshadow' ),
					'deleteError'          => __( 'Failed to delete automation', 'wpshadow' ),
					'runSuccess'           => __( 'Automation executed successfully', 'wpshadow' ),
					'runError'             => __( 'Failed to run automation', 'wpshadow' ),
					'toggleSuccess'        => __( 'Automation status updated', 'wpshadow' ),
					'toggleError'          => __( 'Failed to update automation status', 'wpshadow' ),
					'noActivity'           => __( 'No activity recorded', 'wpshadow' ),
					'errorLoadingActivity' => __( 'Error loading activity', 'wpshadow' ),
					'loadingActivity'      => __( 'Loading activity...', 'wpshadow' ),
					'automationsHeading'   => __( 'Your Automations', 'wpshadow' ),
					'automationsIntro'     => __( 'Manage and monitor your active automations.', 'wpshadow' ),
					'viewDetails'          => __( 'View Details', 'wpshadow' ),
					'runNow'               => __( 'Run Now', 'wpshadow' ),
					'delete'               => __( 'Delete', 'wpshadow' ),
					'createSuccess'        => __( 'Automation created successfully', 'wpshadow' ),
				),
			)
		);

		// Load the dashboard view.
		require_once WPSHADOW_PATH . 'includes/ui/templates/automations-dashboard.php';
	}
}
