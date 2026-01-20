<?php

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function WPSHADOW_is_support_enabled(): bool {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin_file = 'wpshadow/wpshadow.php';

	if ( ! is_plugin_active( $plugin_file ) ) {
		return false;
	}

	$disabled = get_option( 'wpshadow_support_disabled', false );

	return ! $disabled;
}

function WPSHADOW_can_access_dashboard(): bool {
	if ( ! WPSHADOW_is_support_enabled() ) {
		return false;
	}

	$required_cap = get_option( 'wpshadow_capability_dashboard_role', 'manage_options' );

	return current_user_can( $required_cap );
}

function WPSHADOW_can_install_modules(): bool {
	if ( ! WPSHADOW_is_support_enabled() ) {
		return false;
	}

	$install_roles = (array) get_option( 'wpshadow_capability_install_roles', array( 'manage_options' ) );

	foreach ( $install_roles as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}

	return false;
}

function WPSHADOW_can_update_modules(): bool {
	if ( ! WPSHADOW_is_support_enabled() ) {
		return false;
	}

	$update_roles = (array) get_option( 'wpshadow_capability_update_roles', array( 'manage_options' ) );

	foreach ( $update_roles as $cap ) {
		if ( current_user_can( $cap ) ) {
			return true;
		}
	}

	return false;
}

function WPSHADOW_can_manage_settings(): bool {
	if ( ! WPSHADOW_is_support_enabled() ) {
		return false;
	}

	$required_cap = get_option( 'wpshadow_capability_dashboard_role', 'manage_options' );

	return current_user_can( $required_cap );
}

function WPSHADOW_get_dashboard_capability(): string {
	return get_option( 'wpshadow_capability_dashboard_role', 'manage_options' );
}
