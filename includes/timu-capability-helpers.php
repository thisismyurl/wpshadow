<?php
/**
 * Capability Helper Functions
 * Centralized permission checks based on stored settings
 *
 * @package TIMU_WP_SUPPORT_THISISMYURL
 */

namespace TIMU\WP_Support;

/**
 * Check if WP Support plugin is active and enabled.
 *
 * @return bool True if active and enabled, false otherwise.
 */
function timu_is_support_enabled(): bool {
	// Check if plugin is active.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin_file = 'plugin-wp-support-thisismyurl/wp-support-thisismyurl.php';
	
	if ( ! is_plugin_active( $plugin_file ) ) {
		return false;
	}

	// Check if there's a disable flag in options.
	$disabled = get_option( 'timu_support_disabled', false );
	
	return ! $disabled;
}

/**
 * Check if current user can access the Support dashboard.
 *
 * @return bool True if user has permission, false otherwise.
 */
function timu_can_access_dashboard(): bool {
	if ( ! timu_is_support_enabled() ) {
		return false;
	}

	$required_cap = get_option( 'timu_capability_dashboard_role', 'manage_options' );
	
	return current_user_can( $required_cap );
}

/**
 * Check if current user can install modules.
 *
 * @return bool True if user has permission, false otherwise.
 */
function timu_can_install_modules(): bool {
	if ( ! timu_is_support_enabled() ) {
		return false;
	}

	$install_roles = (array) get_option( 'timu_capability_install_roles', array( 'manage_options' ) );
	
	foreach ( $install_roles as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}
	
	return false;
}

/**
 * Check if current user can update modules.
 *
 * @return bool True if user has permission, false otherwise.
 */
function timu_can_update_modules(): bool {
	if ( ! timu_is_support_enabled() ) {
		return false;
	}

	$update_roles = (array) get_option( 'timu_capability_update_roles', array( 'manage_options' ) );
	
	foreach ( $update_roles as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}
	
	return false;
}

/**
 * Check if current user can manage settings.
 *
 * @return bool True if user has permission, false otherwise.
 */
function timu_can_manage_settings(): bool {
	if ( ! timu_is_support_enabled() ) {
		return false;
	}

	// Settings require at minimum the dashboard capability.
	$required_cap = get_option( 'timu_capability_dashboard_role', 'manage_options' );
	
	return current_user_can( $required_cap );
}

/**
 * Get required capability for dashboard access (respects settings).
 *
 * @return string The capability required.
 */
function timu_get_dashboard_capability(): string {
	return get_option( 'timu_capability_dashboard_role', 'manage_options' );
}
