<?php
/**
 * AJAX handlers for Spoke Collection actions
 *
 * Handles install, activate, and deactivate requests for spoke plugins.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX request to install a spoke plugin.
 *
 * @return void
 */
function WPSHADOW_ajax_install_spoke(): void {
	// Verify nonce.
	check_ajax_referer( 'wpshadow_spoke_collection', 'nonce' );

	// Check permissions.
	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error( __( 'You do not have permission to install plugins.', 'plugin-wpshadow' ) );
	}

	// Get spoke identifier.
	$spoke = \WPS\CoreSupport\WPSHADOW_get_post_key( 'spoke' );

	if ( empty( $spoke ) ) {
		wp_send_json_error( __( 'Invalid spoke identifier.', 'plugin-wpshadow' ) );
	}

	// Get spoke slug.
	$spoke_slug = $spoke . '-wpshadow';

	// Check if already installed.
	$plugin_file = $spoke_slug . '/' . $spoke_slug . '.php';

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();
	if ( isset( $plugins[ $plugin_file ] ) ) {
		wp_send_json_success(
			array(
				'message'    => sprintf(
					/* translators: %s: Spoke name */
					__( '%s spoke is already installed.', 'plugin-wpshadow' ),
					strtoupper( $spoke )
				),
				'milestones' => array(),
			)
		);
	}

	// Get module info from registry (for download URL).
	$catalog     = WPSHADOW_Module_Registry::get_catalog_with_status();
	$module_info = null;

	foreach ( $catalog as $module ) {
		if ( isset( $module['slug'] ) && $module['slug'] === $spoke_slug ) {
			$module_info = $module;
			break;
		}
	}

	if ( ! $module_info || empty( $module_info['download_url'] ) ) {
		wp_send_json_error( __( 'Spoke not found in catalog or download URL missing.', 'plugin-wpshadow' ) );
	}

	// Use WPSHADOW_Plugin_Upgrader to install.
	if ( ! class_exists( '\\WPShadow\\WPSHADOW_Plugin_Upgrader' ) ) {
		require_once WPSHADOW_PATH . 'includes/class-wps-plugin-upgrader.php';
	}

	$upgrader = new WPSHADOW_Plugin_Upgrader();
	$result   = $upgrader->install( $module_info['download_url'] );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	}

	// Log installation.
	WPSHADOW_Activity_Logger::log(
		'spoke_installed',
		sprintf( '%s Spoke Installed', strtoupper( $spoke ) ),
		array(
			'spoke'     => $spoke,
			'timestamp' => time(),
		)
	);

	// Check for milestone unlocks.
	$milestones = WPSHADOW_Spoke_Collection::check_milestone_unlocks();

	// Clear module registry cache.
	WPSHADOW_Module_Registry::clear_cache();

	wp_send_json_success(
		array(
			'message'    => sprintf(
				/* translators: %s: Spoke name */
				__( '%s spoke installed successfully!', 'plugin-wpshadow' ),
				strtoupper( $spoke )
			),
			'milestones' => array_values( $milestones ),
		)
	);
}

/**
 * Handle AJAX request to activate a spoke plugin.
 *
 * @return void
 */
function WPSHADOW_ajax_activate_spoke(): void {
	// Verify nonce.
	check_ajax_referer( 'wpshadow_spoke_collection', 'nonce' );

	// Check permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( __( 'You do not have permission to activate plugins.', 'plugin-wpshadow' ) );
	}

	// Get spoke identifier.
	$spoke = \WPS\CoreSupport\WPSHADOW_get_post_key( 'spoke' );

	if ( empty( $spoke ) ) {
		wp_send_json_error( __( 'Invalid spoke identifier.', 'plugin-wpshadow' ) );
	}

	// Get spoke slug.
	$spoke_slug  = $spoke . '-wpshadow';
	$plugin_file = $spoke_slug . '/' . $spoke_slug . '.php';

	// Check if plugin exists.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();
	if ( ! isset( $plugins[ $plugin_file ] ) ) {
		wp_send_json_error( __( 'Spoke plugin not found. Please install it first.', 'plugin-wpshadow' ) );
	}

	// Check if already active.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( $plugin_file ) ) {
		wp_send_json_success(
			array(
				'message'    => sprintf(
					/* translators: %s: Spoke name */
					__( '%s spoke is already active.', 'plugin-wpshadow' ),
					strtoupper( $spoke )
				),
				'milestones' => array(),
			)
		);
	}

	// Activate plugin.
	$result = activate_plugin( $plugin_file );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	}

	// Log activation.
	WPSHADOW_Activity_Logger::log(
		'spoke_activated',
		sprintf( '%s Spoke Activated', strtoupper( $spoke ) ),
		array(
			'spoke'     => $spoke,
			'timestamp' => time(),
		)
	);

	// Check for milestone unlocks.
	$milestones = WPSHADOW_Spoke_Collection::check_milestone_unlocks();

	// Clear module registry cache.
	WPSHADOW_Module_Registry::clear_cache();

	wp_send_json_success(
		array(
			'message'    => sprintf(
				/* translators: %s: Spoke name */
				__( '%s spoke activated successfully!', 'plugin-wpshadow' ),
				strtoupper( $spoke )
			),
			'milestones' => array_values( $milestones ),
		)
	);
}

/**
 * Handle AJAX request to deactivate a spoke plugin.
 *
 * @return void
 */
function WPSHADOW_ajax_deactivate_spoke(): void {
	// Verify nonce.
	check_ajax_referer( 'wpshadow_spoke_collection', 'nonce' );

	// Check permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( __( 'You do not have permission to deactivate plugins.', 'plugin-wpshadow' ) );
	}

	// Get spoke identifier.
	$spoke = \WPS\CoreSupport\WPSHADOW_get_post_key( 'spoke' );

	if ( empty( $spoke ) ) {
		wp_send_json_error( __( 'Invalid spoke identifier.', 'plugin-wpshadow' ) );
	}

	// Get spoke slug.
	$spoke_slug  = $spoke . '-wpshadow';
	$plugin_file = $spoke_slug . '/' . $spoke_slug . '.php';

	// Check if plugin exists.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();
	if ( ! isset( $plugins[ $plugin_file ] ) ) {
		wp_send_json_error( __( 'Spoke plugin not found.', 'plugin-wpshadow' ) );
	}

	// Check if already inactive.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active( $plugin_file ) ) {
		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %s: Spoke name */
					__( '%s spoke is already inactive.', 'plugin-wpshadow' ),
					strtoupper( $spoke )
				),
			)
		);
	}

	// Deactivate plugin.
	deactivate_plugins( $plugin_file );

	// Log deactivation.
	WPSHADOW_Activity_Logger::log(
		'spoke_deactivated',
		sprintf( '%s Spoke Deactivated', strtoupper( $spoke ) ),
		array(
			'spoke'     => $spoke,
			'timestamp' => time(),
		)
	);

	// Clear module registry cache.
	WPSHADOW_Module_Registry::clear_cache();

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %s: Spoke name */
				__( '%s spoke deactivated successfully!', 'plugin-wpshadow' ),
				strtoupper( $spoke )
			),
		)
	);
}

// Register AJAX handlers.
add_action( 'wp_ajax_WPSHADOW_install_spoke', __NAMESPACE__ . '\\WPSHADOW_ajax_install_spoke' );
add_action( 'wp_ajax_WPSHADOW_activate_spoke', __NAMESPACE__ . '\\WPSHADOW_ajax_activate_spoke' );
add_action( 'wp_ajax_WPSHADOW_deactivate_spoke', __NAMESPACE__ . '\\WPSHADOW_ajax_deactivate_spoke' );
