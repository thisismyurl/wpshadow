<?php
/**
 * Vault helper functions for WPShadow Pro.
 *
 * These functions provide access to vault functionality for dashboard widgets
 * and other pro features that depend on vault data.
 *
 * @package WPShadow\Pro
 */

declare(strict_types=1);

namespace WPShadow\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current vault encryption key.
 *
 * Attempts to retrieve the encryption key from:
 * 1. Vault Support module (if available)
 * 2. Local storage option (if configured)
 *
 * @return string|null The encryption key, or null if not found.
 */
function wpshadow_get_vault_key(): ?string {
	if ( class_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault' ) ) {
		// Prefer module-provided key.
		if ( method_exists( '\\WPShadow\\VaultSupport\\WPSHADOW_Vault', 'get_current_key' ) ) {
			$key = \WPShadow\VaultSupport\WPSHADOW_Vault::get_current_key();
			return ! empty( $key ) ? (string) $key : null;
		}
	}
	// Fallback to legacy storage if module not loaded yet.
	if ( defined( 'WPSHADOW_VAULT_KEY' ) && WPSHADOW_VAULT_KEY ) {
		return (string) WPSHADOW_VAULT_KEY;
	}
	$stored_key = get_option( 'wpshadow_vault_enc_key', '' );
	return ! empty( $stored_key ) ? (string) $stored_key : null;
}
