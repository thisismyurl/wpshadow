<?php
/**
 * Vault class alias for core-support plugin.
 *
 * This file delegates all Vault operations to the canonical implementation
 * in vault-support-thisismyurl plugin. No logic duplication.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify vault-support Vault class is available.
if ( ! class_exists( '\\TIMU\\VaultSupport\\TIMU_Vault' ) ) {
	/**
	 * Display admin notice when vault-support Vault class is missing.
	 *
	 * @return void
	 */
	function timu_vault_missing_plugin_notice(): void {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Core Support requires Vault Support to be installed and active with the Vault class available.', 'wordpress-support-thisismyurl' )
		);
	}
	add_action( 'admin_notices', __NAMESPACE__ . '\\timu_vault_missing_plugin_notice' );
	return;
}

// Alias vault-support Vault class into this namespace for backward compatibility.
class_alias( '\\TIMU\\VaultSupport\\TIMU_Vault', __NAMESPACE__ . '\\TIMU_Vault' );
