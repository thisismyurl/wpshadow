<?php
/**
 * Mobile Friendliness Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'read' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'mobile-friendliness' );

// Render header
Tool_View_Base::render_header( __( 'Mobile Friendliness Checker', 'wpshadow' ) );
?>
	<p><?php esc_html_e( 'Run a quick scan to spot mobile-readiness issues like viewport, zoom, and small fonts.', 'wpshadow' ); ?></p>

	<div class="wpshadow-mobile-grid">
		<div class="wpshadow-mobile-panel">
			<h3><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-mobile-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-mobile-path">
							<?php esc_html_e( 'Page Path', 'wpshadow' ); ?>
						</label>
						<div class="wps-flex-gap-10-items-center">
							<span class="wps-p-8-rounded-3" id="mobile-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
							<input type="text" id="wpshadow-mobile-path" name="path" class="regular-text" value="/" placeholder="/about" required />
						</div>
						<span class="wps-help-text">
							<?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean. We fetch the page server-side to check viewport and layout signals.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			
				<p class="submit">
					<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Run Mobile Check', 'wpshadow' ); ?>
					</button>
				</p>

				<div id="wpshadow-mobile-error" class="notice notice-error"></div>
			</form>
		</div>

		<div class="wpshadow-mobile-panel">
			<h3><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Viewport tag with width=device-width', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Zoom allowed (no user-scalable=no)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'No extremely small font sizes', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Avoid fixed widths that force horizontal scroll', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'These are lightweight checks—pair with real device testing for best results.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<div class="wpshadow-mobile-panel wpshadow-mobile-results is-hidden" id="wpshadow-mobile-results">
		<h3><?php esc_html_e( 'Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-mobile-last-url"></span></p>
		<div class="wpshadow-mobile-summary">
			<span class="wpshadow-mobile-pill is-pass" data-mobile-summary="pass"><?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong></span>
			<span class="wpshadow-mobile-pill is-warn" data-mobile-summary="warn"><?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong></span>
			<span class="wpshadow-mobile-pill is-fail" data-mobile-summary="fail"><?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong></span>
		</div>
		<div id="wpshadow-mobile-checks"></div>
	</div>
</div>

<?php Tool_View_Base::render_footer(); ?>
