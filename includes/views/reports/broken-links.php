<?php
/**
 * Broken Links Report
 *
 * Scans pages for broken internal and external links.
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
Tool_View_Base::enqueue_assets( 'broken-links' );

// Render header
Tool_View_Base::render_header( __( 'Broken Link Checker', 'wpshadow' ) );
?>
	<p><?php esc_html_e( 'Check a specific page for broken links in content, images, and navigation.', 'wpshadow' ); ?></p>

	<div class="wpshadow-link-grid">
		<div class="wpshadow-link-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-link-scan-heading">
			<h3 id="wpshadow-link-scan-heading"><?php esc_html_e( 'Scan a URL', 'wpshadow' ); ?></h3>
			<form id="wpshadow-link-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-link-url">
							<?php esc_html_e( 'URL', 'wpshadow' ); ?>
						</label>
						<input type="text" id="wpshadow-link-url" name="url" class="wps-input" value="<?php echo esc_url( trailingslashit( home_url() ) ); ?>" placeholder="<?php echo esc_url( trailingslashit( home_url() ) ); ?>about" required />
						<span class="wps-help-text">
							<?php esc_html_e( 'Enter a full URL or path. We fetch the page server-side to check all links.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			
				<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-link-submit-btn" aria-label="<?php esc_attr_e( 'Run a broken link check for the provided page path', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Check Links', 'wpshadow' ); ?>
				</button>
			</p>

			<!-- Progress Bar Container -->
			<div id="wpshadow-link-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-link-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-link-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-link-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-link-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<div class="wpshadow-link-panel wps-card" role="region" aria-labelledby="wpshadow-link-checklist-heading">
			<h3 id="wpshadow-link-checklist-heading"><?php esc_html_e( 'What we look for', 'wpshadow' ); ?></h3>
			<ul style="list-style: disc; margin-left: 18px;">
				<li><?php esc_html_e( 'Broken internal links (404 errors)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Broken external links (timeouts, errors)', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Missing images and media', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Redirects and moved content', 'wpshadow' ); ?></li>
			</ul>
			<p class="description"><?php esc_html_e( 'Broken links hurt both user experience and SEO rankings. Keep your content clean!', 'wpshadow' ); ?></p>
		</div>
	</div>

	<!-- Results Section (Full Width) -->
	<div class="wpshadow-link-results wps-none wps-card" id="wpshadow-link-results" role="region" aria-live="polite" aria-labelledby="wpshadow-link-results-heading" style="margin-top: 20px;">
		<h3 id="wpshadow-link-results-heading"><?php esc_html_e( 'Scan Results', 'wpshadow' ); ?></h3>
		<p><strong><?php esc_html_e( 'Checked URL:', 'wpshadow' ); ?></strong> <span id="wpshadow-link-last-url"></span></p>
		<div class="wpshadow-link-summary" style="display: flex; gap: 15px; margin: 20px 0;">
			<span class="wpshadow-link-pill is-pass" data-link-summary="pass" style="padding: 8px 16px; border-radius: 4px; background: #f0f6f2; color: #28a745; font-weight: 600;">
				<?php esc_html_e( 'Working', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-link-pill is-warn" data-link-summary="warn" style="padding: 8px 16px; border-radius: 4px; background: #fffbf0; color: #d98300; font-weight: 600;">
				<?php esc_html_e( 'Warnings', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
			<span class="wpshadow-link-pill is-fail" data-link-summary="fail" style="padding: 8px 16px; border-radius: 4px; background: #fdf7f7; color: #dc3545; font-weight: 600;">
				<?php esc_html_e( 'Broken', 'wpshadow' ); ?>: <strong>0</strong>
			</span>
		</div>
		<div id="wpshadow-link-checks"></div>
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
	.wpshadow-link-grid {
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
		'icon'        => 'dashicons-admin-links',
		'style'       => 'default',
	)
);
?>
<?php Tool_View_Base::render_footer(); ?>
