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
		<div class="wpshadow-mobile-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-mobile-scan-heading">
			<h3 id="wpshadow-mobile-scan-heading"><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-mobile-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-mobile-path">
							<?php esc_html_e( 'Page Path', 'wpshadow' ); ?>
						</label>
						<div class="wps-flex-gap-10-items-center">
							<span class="wps-p-8-rounded-3" id="mobile-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
							<input type="text" id="wpshadow-mobile-path" name="path" class="wps-input" value="/" placeholder="/about" required />
						</div>
						<span class="wps-help-text">
							<?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean. We fetch the page server-side to check viewport and layout signals.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			
				<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-mobile-submit-btn" aria-label="<?php esc_attr_e( 'Run a mobile readiness check for the provided page path', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Run Mobile Check', 'wpshadow' ); ?>
				</button>
			</p>

			<!-- Progress Bar Container -->
			<div id="wpshadow-mobile-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-mobile-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-mobile-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-mobile-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-mobile-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<div class="wpshadow-mobile-panel wps-card" role="region" aria-labelledby="wpshadow-mobile-checklist-heading">
			<h3 id="wpshadow-mobile-checklist-heading"><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Viewport tag with width=device-width', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Zoom allowed (no user-scalable=no)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'No extremely small font sizes', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Avoid fixed widths that force horizontal scroll', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'These are lightweight checks—pair with real device testing for best results.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<!-- Results Section (Full Width) -->
	<div class="wpshadow-mobile-results wps-none wps-card" id="wpshadow-mobile-results" role="region" aria-live="polite" aria-labelledby="wpshadow-mobile-results-heading" style="margin-top: 20px;">
		<h3 id="wpshadow-mobile-results-heading"><?php esc_html_e( 'Scan Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-mobile-last-url"></span></p>
		<div class="wpshadow-mobile-summary" style="display: flex; gap: 15px; margin: 20px 0;">
			<span class="wpshadow-mobile-pill is-pass" data-mobile-summary="pass" style="padding: 8px 16px; border-radius: 4px; background: #f0f6f2; color: #28a745; font-weight: 600;">
				<?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-mobile-pill is-warn" data-mobile-summary="warn" style="padding: 8px 16px; border-radius: 4px; background: #fffbf0; color: #d98300; font-weight: 600;">
				<?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-mobile-pill is-fail" data-mobile-summary="fail" style="padding: 8px 16px; border-radius: 4px; background: #fdf7f7; color: #dc3545; font-weight: 600;">
				<?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
		</div>
		<div id="wpshadow-mobile-checks"></div>
	</div>
</div>

<style>
	@keyframes wps-spin {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}
	.wps-spin {
		animation: wps-spin 1s linear infinite;
		display: inline-block;
	}
	.wpshadow-mobile-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 20px;
		margin-bottom: 20px;
	}
</style>

<?php Tool_View_Base::render_footer(); ?>

