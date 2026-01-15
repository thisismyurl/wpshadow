<?php
/**
 * Helper wrappers for WPS settings and capabilities.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function WPSHADOW_get_setting( string $module, string $key, $default = null, bool $network = false ) {
	return WPSHADOW_Settings::get( $module, $key, $default, $network );
}

function WPSHADOW_update_setting( string $module, string $key, $value, bool $network = false ): bool {
	return WPSHADOW_Settings::update( $module, $key, $value, $network );
}

function WPSHADOW_delete_setting( string $module, string $key, bool $network = false ): bool {
	return WPSHADOW_Settings::delete( $module, $key, $network );
}

function WPSHADOW_can_override_setting( string $module, string $key ): bool {
	return WPSHADOW_Settings::can_override( $module, $key );
}

function WPSHADOW_register_capability( string $module, string $capability, string $wp_capability ): bool {
	return WPSHADOW_Capabilities::register_capability( $module, $capability, $wp_capability );
}

function WPSHADOW_user_can( string $module, string $capability, ?int $user_id = null ): bool {
	return WPSHADOW_Capabilities::user_can( $module, $capability, $user_id );
}

function WPSHADOW_get_module_capabilities( string $module ): array {
	return WPSHADOW_Capabilities::get_module_capabilities( $module );
}

function WPSHADOW_register_module( array $module ): bool {
	return WPSHADOW_Module_Registry::register( $module );
}
