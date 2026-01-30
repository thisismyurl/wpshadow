<?php
/**
 * Accessibility Audit Report
 *
 * Comprehensive WCAG compliance, ARIA usage, keyboard navigation,
 * and color contrast analysis.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 1.2602.0000
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
Tool_View_Base::enqueue_assets( 'a11y-audit' );

// Render header
Tool_View_Base::render_header( __( 'Accessibility Audit', 'wpshadow' ) );
?>
	<p><?php esc_html_e( 'Scan a page for accessibility issues, WCAG compliance, and color contrast violations.', 'wpshadow' ); ?></p>

	<div class="wpshadow-a11y-grid">
		<div class="wpshadow-a11y-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-a11y-scan-heading">
			<h3 id="wpshadow-a11y-scan-heading"><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-a11y-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-a11y-path">
							<?php esc_html_e( 'Page Path', 'wpshadow' ); ?>
						</label>
						<div class="wps-flex-gap-10-items-center">
							<span class="wps-p-8-rounded-3" id="a11y-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
							<input type="text" id="wpshadow-a11y-path" name="path" class="wps-input" value="/" placeholder="/about" required />
						</div>
						<span class="wps-help-text">
							<?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean. We fetch the page server-side to check accessibility compliance.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			
				<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-a11y-submit-btn" aria-label="<?php esc_attr_e( 'Run an accessibility scan for the provided page path', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Run Accessibility Scan', 'wpshadow' ); ?>
				</button>
			</p>

			<!-- Progress Bar Container -->
			<div id="wpshadow-a11y-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-a11y-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-a11y-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-a11y-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-a11y-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<div class="wpshadow-a11y-panel wps-card" role="region" aria-labelledby="wpshadow-a11y-checklist-heading">
			<h3 id="wpshadow-a11y-checklist-heading"><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Alt text on all images for screen readers', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Proper heading hierarchy (H1, H2, H3)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'ARIA labels on interactive elements', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Sufficient color contrast ratios', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Keyboard navigation support', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'These checks help ensure your site is usable by everyone, including people using assistive technologies.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<!-- Results Section (Full Width) -->
	<div class="wpshadow-a11y-results wps-none wps-card" id="wpshadow-a11y-results" role="region" aria-live="polite" aria-labelledby="wpshadow-a11y-results-heading" style="margin-top: 20px;">
		<h3 id="wpshadow-a11y-results-heading"><?php esc_html_e( 'Scan Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-a11y-last-url"></span></p>
		<div class="wpshadow-a11y-summary" style="display: flex; gap: 15px; margin: 20px 0;">
			<span class="wpshadow-a11y-pill is-pass" data-a11y-summary="pass" style="padding: 8px 16px; border-radius: 4px; background: #f0f6f2; color: #28a745; font-weight: 600;">
				<?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-a11y-pill is-warn" data-a11y-summary="warn" style="padding: 8px 16px; border-radius: 4px; background: #fffbf0; color: #d98300; font-weight: 600;">
				<?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-a11y-pill is-fail" data-a11y-summary="fail" style="padding: 8px 16px; border-radius: 4px; background: #fdf7f7; color: #dc3545; font-weight: 600;">
				<?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
		</div>
		<div id="wpshadow-a11y-checks"></div>
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
	.wpshadow-a11y-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 20px;
		margin-bottom: 20px;
	}
</style>

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Want to scan multiple URLs at once?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro lets you batch-scan entire sections of your site in one go.', 'wpshadow' ),
		'features'    => array(
			__( 'Scan multiple URLs per session', 'wpshadow' ),
			__( 'Batch processing for large sites', 'wpshadow' ),
			__( 'Export results to CSV', 'wpshadow' ),
			__( 'Priority support and updates', 'wpshadow' ),
		),
		'cta_text'    => __( 'Learn More About WPShadow Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-universal-access',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
