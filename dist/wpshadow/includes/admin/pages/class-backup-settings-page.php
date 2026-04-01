<?php
/**
 * Legacy Backup Settings Page Wrapper
 *
 * Maintains backward compatibility by delegating to the WPShadow Vault Light
 * settings page.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy Backup Settings Page Wrapper
 *
 * @since 0.6093.1200
 */
class Backup_Settings_Page {

	/**
	 * Render the legacy settings page.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		if ( file_exists( WPSHADOW_PATH . 'includes/admin/pages/class-vault-light-settings-page.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/admin/pages/class-vault-light-settings-page.php';
		}

		if ( class_exists( 'WPShadow\\Admin\\Pages\\Vault_Light_Settings_Page' ) ) {
			\WPShadow\Admin\Pages\Vault_Light_Settings_Page::render();
			return;
		}

		wp_die( esc_html__( 'WPShadow Vault Light settings are unavailable.', 'wpshadow' ) );
	}
}
