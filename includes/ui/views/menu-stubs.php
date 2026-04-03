<?php
/**
 * Menu Callback Stubs
 *
 * Temporary stub functions for menu callbacks that don't have dedicated files yet.
 * These prevent fatal errors when menus are registered but the actual page isn't loaded.
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_render_findings' ) ) {
	/**
	 * Render Findings page
	 *
	 * @since 0.6093.1200
	 */
	function wpshadow_render_findings() {
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Findings', 'wpshadow' ),
				__( 'Review site findings and their current status.', 'wpshadow' ),
				'dashicons-grid-view'
			);
			?>
		</div>
		<?php
	}
}

// Legacy compatibility alias
if ( ! function_exists( 'wpshadow_render_action_items' ) ) {
	/**
	 * Legacy function name - redirects to wpshadow_render_findings()
	 *
	 * @deprecated Use wpshadow_render_findings() instead
	 */
	function wpshadow_render_action_items() {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '0.6030.2200', 'wpshadow_render_findings' );
		}
		wpshadow_render_findings();
	}
}

if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	/**
	 * Legacy function name - redirects to dashboard.
	 *
	 * @deprecated Utilities page removed.
	 */
	function wpshadow_render_tools() {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '0.6093.1200', 'wpshadow_render_dashboard' );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
		exit;
	}
}

if ( ! function_exists( 'wpshadow_render_settings' ) ) {
	/**
	 * Render the WPShadow Settings page.
	 *
	 * @since 0.6093.1200
	 */
	function wpshadow_render_settings() {
		$settings_view = WPSHADOW_PATH . 'includes/ui/views/settings-page.php';
		if ( file_exists( $settings_view ) ) {
			require_once $settings_view;
			return;
		}

		// Fallback when view file is missing.
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Settings', 'wpshadow' ),
				__( 'Settings page could not be loaded.', 'wpshadow' ),
				'dashicons-admin-settings'
			);
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wpshadow_render_vault_lite' ) ) {
	/**
	 * Render the Vault Lite page.
	 *
	 * @since 0.6093.1200
	 */
	function wpshadow_render_vault_lite() {
		$vault_view = WPSHADOW_PATH . 'includes/ui/views/vault-lite-page.php';
		if ( file_exists( $vault_view ) ) {
			require_once $vault_view;
			return;
		}

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Vault Lite', 'wpshadow' ),
				__( 'Vault Lite page could not be loaded.', 'wpshadow' ),
				'dashicons-backup'
			);
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wpshadow_enqueue_settings_assets' ) ) {
	/**
	 * Enqueue CSS and JS for the Settings and Vault Lite pages.
	 *
	 * @since 0.6093.1200
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	function wpshadow_enqueue_settings_assets( string $hook_suffix ): void {
		// Only load on WPShadow settings and Vault Lite pages.
		if ( false === strpos( $hook_suffix, 'wpshadow-settings' ) && false === strpos( $hook_suffix, 'wpshadow-vault-lite' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-settings-page',
			WPSHADOW_URL . 'assets/css/settings-page.css',
			array(),
			file_exists( WPSHADOW_PATH . 'assets/css/settings-page.css' )
				? (string) filemtime( WPSHADOW_PATH . 'assets/css/settings-page.css' )
				: WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-settings-page',
			WPSHADOW_URL . 'assets/js/settings-page.js',
			array( 'jquery' ),
			file_exists( WPSHADOW_PATH . 'assets/js/settings-page.js' )
				? (string) filemtime( WPSHADOW_PATH . 'assets/js/settings-page.js' )
				: WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-settings-page',
			'wpshadowSettingsData',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'adminNonce'        => wp_create_nonce( 'wpshadow_admin' ),
				'scanSettingsNonce' => wp_create_nonce( 'wpshadow_scan_settings' ),
				'i18n'              => array(
					'saving'              => __( 'Saving…', 'wpshadow' ),
					'saved'               => __( 'Saved', 'wpshadow' ),
					'saveError'           => __( 'Save failed', 'wpshadow' ),
					'preferencesSaved'    => __( 'Preferences saved.', 'wpshadow' ),
					'preferencesSaveFail' => __( 'Could not save preferences.', 'wpshadow' ),
					'consentSnoozed'      => __( 'Consent prompt snoozed for 30 days.', 'wpshadow' ),
					'refreshFailed'       => __( 'Failed to refresh summary.', 'wpshadow' ),
					'noItemsFound'        => __( 'No items found.', 'wpshadow' ),
					'inventoryLoadFailed' => __( 'Failed to load inventory.', 'wpshadow' ),
					'exporting'           => __( 'Exporting...', 'wpshadow' ),
					'exportComplete'      => __( 'Export complete.', 'wpshadow' ),
					'exportFailed'        => __( 'Export failed.', 'wpshadow' ),
				),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_settings_assets' );

