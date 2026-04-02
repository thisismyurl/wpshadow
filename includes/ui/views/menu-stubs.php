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

