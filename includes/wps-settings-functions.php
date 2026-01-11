<?php
/**
 * Helper wrappers for WPS settings and capabilities.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function WPS_get_setting( string $module, string $key, $default = null, bool $network = false ) {
	return WPS_Settings::get( $module, $key, $default, $network );
}

function WPS_update_setting( string $module, string $key, $value, bool $network = false ): bool {
	return WPS_Settings::update( $module, $key, $value, $network );
}

function WPS_delete_setting( string $module, string $key, bool $network = false ): bool {
	return WPS_Settings::delete( $module, $key, $network );
}

function WPS_can_override_setting( string $module, string $key ): bool {
	return WPS_Settings::can_override( $module, $key );
}

function WPS_register_capability( string $module, string $capability, string $wp_capability ): bool {
	return WPS_Capabilities::register_capability( $module, $capability, $wp_capability );
}

function WPS_user_can( string $module, string $capability, ?int $user_id = null ): bool {
	return WPS_Capabilities::user_can( $module, $capability, $user_id );
}

function WPS_get_module_capabilities( string $module ): array {
	return WPS_Capabilities::get_module_capabilities( $module );
}

function WPS_register_module( array $module ): bool {
	return WPS_Module_Registry::register( $module );
}
