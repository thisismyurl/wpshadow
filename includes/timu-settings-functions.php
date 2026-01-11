<?php
/**
 * Helper wrappers for TIMU settings and capabilities.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function timu_get_setting( string $module, string $key, $default = null, bool $network = false ) {
	return TIMU_Settings::get( $module, $key, $default, $network );
}

function timu_update_setting( string $module, string $key, $value, bool $network = false ): bool {
	return TIMU_Settings::update( $module, $key, $value, $network );
}

function timu_delete_setting( string $module, string $key, bool $network = false ): bool {
	return TIMU_Settings::delete( $module, $key, $network );
}

function timu_can_override_setting( string $module, string $key ): bool {
	return TIMU_Settings::can_override( $module, $key );
}

function timu_register_capability( string $module, string $capability, string $wp_capability ): bool {
	return TIMU_Capabilities::register_capability( $module, $capability, $wp_capability );
}

function timu_user_can( string $module, string $capability, ?int $user_id = null ): bool {
	return TIMU_Capabilities::user_can( $module, $capability, $user_id );
}

function timu_get_module_capabilities( string $module ): array {
	return TIMU_Capabilities::get_module_capabilities( $module );
}

function timu_register_module( array $module ): bool {
	return TIMU_Module_Registry::register( $module );
}
