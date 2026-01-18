<?php
/**
 * System Report view template.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap wps-system-report-page">
	<h1><?php esc_html_e( 'System Report Generator', 'wpshadow' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'Generate a comprehensive system report for debugging and support. All data is generated locally and sanitized to remove sensitive information.', 'wpshadow' ); ?>
	</p>

	<div class="wps-report-actions">
		<div class="wps-report-buttons">
			<h2><?php esc_html_e( 'Generate Report', 'wpshadow' ); ?></h2>
			
			<div class="button-group">
				<button type="button" class="button button-primary" id="wps-generate-json">
					<span class="dashicons dashicons-media-code"></span>
					<?php esc_html_e( 'Generate JSON', 'wpshadow' ); ?>
				</button>
				
				<button type="button" class="button button-primary" id="wps-generate-txt">
					<span class="dashicons dashicons-media-text"></span>
					<?php esc_html_e( 'Generate TXT', 'wpshadow' ); ?>
				</button>
				
				<button type="button" class="button button-primary" id="wps-generate-pdf">
					<span class="dashicons dashicons-media-document"></span>
					<?php esc_html_e( 'Generate PDF', 'wpshadow' ); ?>
				</button>
			</div>

			<div class="wps-report-status" style="display: none;">
				<span class="spinner is-active"></span>
				<span class="status-text"></span>
			</div>
		</div>

		<div class="wps-shareable-link-section">
			<h2><?php esc_html_e( 'Shareable Link', 'wpshadow' ); ?></h2>
			
			<p class="description">
				<?php esc_html_e( 'Create a private, time-limited link to share the report. Link expires in 7 days.', 'wpshadow' ); ?>
			</p>

			<div class="wps-password-section">
				<label for="wps-link-password">
					<?php esc_html_e( 'Optional Password:', 'wpshadow' ); ?>
				</label>
				<input type="password" id="wps-link-password" class="regular-text" placeholder="<?php esc_attr_e( 'Leave empty for no password', 'wpshadow' ); ?>" />
			</div>

			<button type="button" class="button button-secondary" id="wps-create-link">
				<span class="dashicons dashicons-admin-links"></span>
				<?php esc_html_e( 'Create Shareable Link', 'wpshadow' ); ?>
			</button>

			<div class="wps-link-result" style="display: none;">
				<div class="wps-link-url">
					<input type="text" readonly class="regular-text" id="wps-shareable-url" />
					<button type="button" class="button" id="wps-copy-link">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Copy', 'wpshadow' ); ?>
					</button>
				</div>
				<p class="wps-link-expires">
					<?php esc_html_e( 'Expires:', 'wpshadow' ); ?>
					<strong id="wps-link-expires-at"></strong>
				</p>
			</div>
		</div>
	</div>

	<div class="wps-report-output" style="display: none;">
		<h2><?php esc_html_e( 'Report Output', 'wpshadow' ); ?></h2>
		
		<div class="wps-report-toolbar">
			<button type="button" class="button" id="wps-copy-report">
				<span class="dashicons dashicons-clipboard"></span>
				<?php esc_html_e( 'Copy to Clipboard', 'wpshadow' ); ?>
			</button>
			
			<button type="button" class="button" id="wps-download-report">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Download', 'wpshadow' ); ?>
			</button>
		</div>

		<textarea readonly id="wps-report-content" rows="20"></textarea>
	</div>

	<div class="wps-report-info">
		<h2><?php esc_html_e( 'What\'s Included?', 'wpshadow' ); ?></h2>
		
		<ul class="wps-info-list">
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'WordPress, PHP, and MySQL versions', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Active theme with version', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'All plugins (active/inactive) with versions', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Server configuration (memory, execution time, upload limits)', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Error log (last 100 lines, sanitized)', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Database size and largest tables', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'File permissions (wp-content, uploads)', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cron status', 'wpshadow' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Rewrite rules status', 'wpshadow' ); ?></li>
			<?php if ( is_multisite() ) : ?>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multisite configuration', 'wpshadow' ); ?></li>
			<?php endif; ?>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'wp-config.php constants (sanitized)', 'wpshadow' ); ?></li>
		</ul>

		<div class="wps-privacy-notice">
			<h3><?php esc_html_e( 'Privacy & Security', 'wpshadow' ); ?></h3>
			<ul>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Reports generated locally only', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Sensitive data automatically sanitized (passwords, API keys, salts)', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Shareable links expire after 7 days', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Admin-only access', 'wpshadow' ); ?></li>
			</ul>
		</div>
	</div>
</div>
