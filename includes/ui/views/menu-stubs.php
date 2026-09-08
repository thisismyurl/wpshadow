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
	 * @since 0.6095
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

if ( ! function_exists( 'wpshadow_render_workflow_builder' ) ) {
	/**
	 * Render the Automations (Workflow) page.
	 *
	 * Stub renderer for the gated Automations page. Registered by the menu manager
	 * once the core pages are released; without this the admin.php callback resolves
	 * to an undefined function and fatals under WP_DEBUG.
	 *
	 * @since 1.6183
	 */
	function wpshadow_render_workflow_builder() {
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Automations', 'wpshadow' ),
				__( 'Automation, scheduled tasks, and workflows.', 'wpshadow' ),
				'dashicons-controls-repeat'
			);
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wpshadow_render_settings' ) ) {
	/**
	 * Render the Shadow Settings page.
	 *
	 * @since 0.6095
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

if ( ! function_exists( 'wpshadow_enqueue_settings_assets' ) ) {
	/**
	 * Enqueue CSS and JS for the Settings page.
	 *
	 * @since 0.6095
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	function wpshadow_enqueue_settings_assets( string $hook_suffix ): void {
		// Only load on the Shadow by Christopher Ross settings page.
		if ( false === strpos( $hook_suffix, 'wpshadow-settings' ) ) {
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

