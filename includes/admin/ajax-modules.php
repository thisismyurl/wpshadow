<?php
/**
 * AJAX handlers for module operations extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX toggle module request.
 *
 * @return void
 */
function WPSHADOW_ajax_toggle_module(): void {
	check_ajax_referer( 'wpshadow_toggle_module', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
	}

	$slug    = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$enabled = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];
	$network = isset( $_POST['network'] ) && 'true' === $_POST['network'] && is_multisite();

	if ( empty( $slug ) ) {
		wp_send_json_error( array( 'message' => __( 'We couldn\'t find that module.', 'plugin-wpshadow' ) ) );
	}

	// Update WPSHADOW_module_toggles array (unified toggle system).
	$toggles          = get_option( 'wpshadow_module_toggles', array() );
	$toggles[ $slug ] = $enabled ? 1 : 0;

	$deactivated = array();
	$remembered  = array();

	// Handle cascade deactivation of dependents.
	if ( ! $enabled ) {
		$deactivated = WPSHADOW_Module_Toggles::cascade_deactivate( $slug );
		if ( ! empty( $deactivated ) ) {
			// Reload toggles after cascade.
			$toggles = get_option( 'wpshadow_module_toggles', array() );
		}
	}

	// Handle restoration check on activation.
	if ( $enabled ) {
		$remembered = WPSHADOW_Module_Toggles::get_remembered_deactivated( $slug );
	}

	$success = update_option( 'wpshadow_module_toggles', $toggles );

	// Clear catalog cache so submenu regenerates.
	WPSHADOW_Module_Registry::clear_cache();

	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wpshadow' );

	if ( $success ) {
		// If module is being enabled, inherit parent dashboard layout.
		if ( $enabled ) {
			WPSHADOW_Dashboard_Layout::on_module_activated( $slug, $network );
			// Fire action for achievement badges and other hooks.
			do_action( 'wpshadow_module_activated', (int) $user->ID, $slug );
		}

		WPSHADOW_Vault::add_log(
			'info',
			0,
			sprintf( 'Module %1$s %2$s', $slug, $enabled ? 'enabled' : 'disabled' ),
			'module_toggle',
			array(
				'task'    => 'module_toggle',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);

		// Prepare response data.
		$response_data = array( 'message' => __( 'Module settings updated.', 'plugin-wpshadow' ) );

		// Add cascade info if modules were auto-deactivated.
		if ( ! empty( $deactivated ) ) {
			$response_data['deactivated'] = $deactivated;
		}

		// Add restoration prompt if remembered dependents exist.
		if ( ! empty( $remembered ) ) {
			$response_data['remembered'] = $remembered;
		}

		wp_send_json_success( $response_data );
	} else {
		WPSHADOW_Vault::add_log(
			'error',
			0,
			sprintf( 'Failed to toggle %s', $slug ),
			'module_toggle',
			array(
				'task'    => 'module_toggle',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Settings didn\'t save. Let\'s try that again.', 'plugin-wpshadow' ) ) );
	}
}

/**
 * Handle AJAX install module request.
 *
 * @return void
 */
function WPSHADOW_ajax_install_module(): void {
	check_ajax_referer( 'wpshadow_module_action', 'nonce' );

	if ( ! WPSHADOW_can_install_modules() ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wpshadow' );

	if ( empty( $slug ) ) {
		WPSHADOW_Vault::add_log( 'error', 0, __( 'Please select a module to install.', 'plugin-wpshadow' ), 'module_install' );
		wp_send_json_error( array( 'message' => __( 'Please select a module to install.', 'plugin-wpshadow' ) ) );
	}

	$catalog = WPSHADOW_Module_Registry::get_catalog_with_status();
	$module  = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) ) {
		WPSHADOW_Vault::add_log(
			'error',
			0,
			sprintf( 'Install failed: no download for %s', $slug ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'No download available for this module.', 'plugin-wpshadow' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! WP_Filesystem() ) {
		WPSHADOW_Vault::add_log(
			'error',
			0,
			sprintf( 'Install failed: filesystem credentials needed for %s', $slug ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'File system credentials are required to install plugins.', 'plugin-wpshadow' ) ) );
	}

	$skin     = new \Automatic_Upgrader_Skin();
	$upgrader = new \Plugin_Upgrader( $skin );
	$download = wpshadow_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Installation didn\'t finish. Let\'s try again.', 'plugin-wpshadow' );
		WPSHADOW_Vault::add_log(
			'error',
			0,
			wp_strip_all_tags( $message ),
			'module_install',
			array(
				'task'    => 'module_install',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => $message ) );
	}

	// Attempt activation.
	$plugin_file = wpshadow_find_plugin_file_by_slug( $slug );
	if ( $plugin_file ) {
		$network_wide = is_multisite() && is_network_admin();
		$activation   = activate_plugin( $plugin_file, '', $network_wide, false );
		if ( is_wp_error( $activation ) ) {
			do_action(
				'wpshadow_catalog_install_warning',
				array(
					'slug'    => $slug,
					'message' => $activation->get_error_message(),
				)
			);
		} else {
			// Module activated successfully, inherit dashboard layout.
			WPSHADOW_Dashboard_Layout::on_module_activated( $slug, $network_wide );
		}
	}

	WPSHADOW_Module_Registry::clear_cache();
	WPSHADOW_Module_Registry::discover_modules();
	WPSHADOW_Module_Registry::load_catalog();

	WPSHADOW_Vault::add_log(
		'info',
		0,
		sprintf( 'Module installed: %s', $slug ),
		'module_install',
		array(
			'task'    => 'module_install',
			'file'    => $slug,
			'user'    => $user_name,
			'user_id' => (int) $user->ID,
		)
	);

	wp_send_json_success( array( 'message' => __( 'Module installed.', 'plugin-wpshadow' ) ) );
}

/**
 * Handle AJAX update module request.
 *
 * @return void
 */
function WPSHADOW_ajax_update_module(): void {
	check_ajax_referer( 'wpshadow_module_action', 'nonce' );

	if ( ! WPSHADOW_can_update_modules() ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
	}

	$slug      = sanitize_text_field( wp_unslash( $_POST['slug'] ?? '' ) );
	$user      = wp_get_current_user();
	$user_name = $user && $user->exists() ? $user->display_name : __( 'System', 'plugin-wpshadow' );

	if ( empty( $slug ) ) {
		WPSHADOW_Vault::add_log( 'error', 0, __( 'Please select a module to update.', 'plugin-wpshadow' ), 'module_update' );
		wp_send_json_error( array( 'message' => __( 'Please select a module to update.', 'plugin-wpshadow' ) ) );
	}

	$catalog   = WPSHADOW_Module_Registry::get_catalog_with_status();
	$installed = WPSHADOW_Module_Registry::get_module( $slug );
	$module    = $catalog[ $slug ] ?? null;

	if ( empty( $module ) || empty( $module['download_url'] ) || empty( $installed['file'] ) ) {
		WPSHADOW_Vault::add_log(
			'error',
			0,
			sprintf( 'Update failed: missing data for %s', $slug ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'Update information is missing for this module.', 'plugin-wpshadow' ) ) );
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( ! WP_Filesystem() ) {
		WPSHADOW_Vault::add_log(
			'error',
			0,
			sprintf( 'Update failed: filesystem credentials needed for %s', $slug ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => __( 'File system credentials are required to update plugins.', 'plugin-wpshadow' ) ) );
	}

	$skin     = new \Automatic_Upgrader_Skin();
	$upgrader = new \Plugin_Upgrader( $skin );

	// Allow overwriting existing destination during update.
	$filter = function ( array $options ): array {
		$options['clear_destination']           = true;
		$options['abort_if_destination_exists'] = false;
		return $options;
	};
	add_filter( 'upgrader_package_options', $filter );

	$download = wpshadow_resolve_download_url( $module );
	$result   = $upgrader->install( $download );

	// Remove filter after run.
	remove_filter( 'upgrader_package_options', $filter );

	if ( is_wp_error( $result ) || ! $result ) {
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Update didn\'t complete. Let\'s try again.', 'plugin-wpshadow' );
		WPSHADOW_Vault::add_log(
			'error',
			0,
			wp_strip_all_tags( $message ),
			'module_update',
			array(
				'task'    => 'module_update',
				'file'    => $slug,
				'user'    => $user_name,
				'user_id' => (int) $user->ID,
			)
		);
		wp_send_json_error( array( 'message' => $message ) );
	}

	WPSHADOW_Module_Registry::clear_cache();
	WPSHADOW_Module_Registry::discover_modules();
	WPSHADOW_Module_Registry::load_catalog();

	WPSHADOW_Vault::add_log(
		'info',
		0,
		sprintf( 'Module updated: %s', $slug ),
		'module_update',
		array(
			'task'    => 'module_update',
			'file'    => $slug,
			'user'    => $user_name,
			'user_id' => (int) $user->ID,
		)
	);

	wp_send_json_success( array( 'message' => __( 'Module updated.', 'plugin-wpshadow' ) ) );
}

/**
 * Handle network license broadcast via AJAX.
 *
 * @return void
 */
function WPSHADOW_ajax_broadcast_license(): void {
	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh and try again.', 'plugin-wpshadow' ) ) );
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpshadow_broadcast_license' ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh and try again.', 'plugin-wpshadow' ) ) );
	}

	if ( ! is_multisite() ) {
		wp_send_json_error( array( 'message' => __( 'Multisite not enabled.', 'plugin-wpshadow' ) ) );
	}

	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
	}

	$key            = \WPShadow\WPSHADOW_get_post_text( 'key' );
	$site_ids_json  = \WPShadow\WPSHADOW_get_post_text( 'site_ids', '[]' );
	$auto_broadcast = \WPShadow\WPSHADOW_get_post_int( 'auto_broadcast' );

	if ( empty( $key ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your license key.', 'plugin-wpshadow' ) ) );
	}

	// Parse site IDs from JSON.
	$site_ids = (array) json_decode( $site_ids_json, true );
	$site_ids = array_map( 'absint', array_filter( $site_ids ) );

	if ( empty( $site_ids ) ) {
		wp_send_json_error( array( 'message' => __( 'No sites selected.', 'plugin-wpshadow' ) ) );
	}

	// Call the license broadcast method.
	$result = WPSHADOW_License::broadcast_network_key( $key, $site_ids, (bool) $auto_broadcast );

	wp_send_json_success( $result );
}
