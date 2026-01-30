<?php
/**
 * Color Contrast Checker Tool
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
Tool_View_Base::enqueue_assets( 'color-contrast-checker' );

// Render header
Tool_View_Base::render_header( __( 'Color Contrast Checker', 'wpshadow' ) );
?>
	<p><?php esc_html_e( 'Analyze color contrast ratios on a specific page to ensure WCAG compliance.', 'wpshadow' ); ?></p>

	<div class="wpshadow-contrast-grid">
		<div class="wpshadow-contrast-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-contrast-scan-heading">
			<h3 id="wpshadow-contrast-scan-heading"><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-contrast-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-contrast-path">
							<?php esc_html_e( 'Page Path', 'wpshadow' ); ?>
						</label>
						<div class="wps-flex-gap-10-items-center">
							<span class="wps-p-8-rounded-3" id="contrast-site-domain"><?php echo esc_html( untrailingslashit( home_url() ) ); ?></span>
							<input type="text" id="wpshadow-contrast-path" name="path" class="wps-input" value="/" placeholder="/about" required />
						</div>
						<span class="wps-help-text">
							<?php esc_html_e( 'Enter the page path (e.g., /about, /contact). You can also paste a full URL and it will auto-clean. We analyze the page for color contrast issues.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			
				<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-contrast-submit-btn" aria-label="<?php esc_attr_e( 'Run a color contrast check for the provided page path', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Check Contrast', 'wpshadow' ); ?>
				</button>
			</p>

			<!-- Progress Bar Container -->
			<div id="wpshadow-contrast-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-contrast-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-contrast-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-contrast-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-contrast-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<div class="wpshadow-contrast-panel wps-card" role="region" aria-labelledby="wpshadow-contrast-checklist-heading">
			<h3 id="wpshadow-contrast-checklist-heading"><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Text vs background contrast ratios', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'WCAG AA compliance (4.5:1 minimum)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'WCAG AAA compliance (7:1 for best accessibility)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Large text contrast (3:1 minimum)', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'Poor color contrast makes content difficult to read for users with visual impairments or in bright lighting conditions.', 'wpshadow' ); ?></p>
		</div>
	</div>

	<!-- Results Section (Full Width) -->
	<div class="wpshadow-contrast-results wps-none wps-card" id="wpshadow-contrast-results" role="region" aria-live="polite" aria-labelledby="wpshadow-contrast-results-heading" style="margin-top: 20px;">
		<h3 id="wpshadow-contrast-results-heading"><?php esc_html_e( 'Scan Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-contrast-last-url"></span></p>
		<div class="wpshadow-contrast-summary" style="display: flex; gap: 15px; margin: 20px 0;">
			<span class="wpshadow-contrast-pill is-pass" data-contrast-summary="pass" style="padding: 8px 16px; border-radius: 4px; background: #f0f6f2; color: #28a745; font-weight: 600;">
				<?php esc_html_e( 'Passes', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-contrast-pill is-warn" data-contrast-summary="warn" style="padding: 8px 16px; border-radius: 4px; background: #fffbf0; color: #d98300; font-weight: 600;">
				<?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-contrast-pill is-fail" data-contrast-summary="fail" style="padding: 8px 16px; border-radius: 4px; background: #fdf7f7; color: #dc3545; font-weight: 600;">
				<?php esc_html_e( 'Fails', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
		</div>
		<div id="wpshadow-contrast-checks"></div>
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
	.wpshadow-contrast-grid {
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
		'icon'        => 'dashicons-visibility',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>

