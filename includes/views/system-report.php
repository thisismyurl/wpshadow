<?php
/**
 * System Report view template.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap wps-system-report-page">
	<h1><?php esc_html_e( 'System Report Generator', 'plugin-wp-support-thisismyurl' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'Generate a comprehensive system report for debugging and support. All data is generated locally and sanitized to remove sensitive information.', 'plugin-wp-support-thisismyurl' ); ?>
	</p>

	<div class="wps-report-actions">
		<div class="wps-report-buttons">
			<h2><?php esc_html_e( 'Generate Report', 'plugin-wp-support-thisismyurl' ); ?></h2>
			
			<div class="button-group">
				<button type="button" class="button button-primary" id="wps-generate-json">
					<span class="dashicons dashicons-media-code"></span>
					<?php esc_html_e( 'Generate JSON', 'plugin-wp-support-thisismyurl' ); ?>
				</button>
				
				<button type="button" class="button button-primary" id="wps-generate-txt">
					<span class="dashicons dashicons-media-text"></span>
					<?php esc_html_e( 'Generate TXT', 'plugin-wp-support-thisismyurl' ); ?>
				</button>
				
				<button type="button" class="button button-primary" id="wps-generate-pdf">
					<span class="dashicons dashicons-media-document"></span>
					<?php esc_html_e( 'Generate PDF', 'plugin-wp-support-thisismyurl' ); ?>
				</button>
			</div>

			<div class="wps-report-status" style="display: none;">
				<span class="spinner is-active"></span>
				<span class="status-text"></span>
			</div>
		</div>

		<div class="wps-shareable-link-section">
			<h2><?php esc_html_e( 'Shareable Link', 'plugin-wp-support-thisismyurl' ); ?></h2>
			
			<p class="description">
				<?php esc_html_e( 'Create a private, time-limited link to share the report. Link expires in 7 days.', 'plugin-wp-support-thisismyurl' ); ?>
			</p>

			<div class="wps-password-section">
				<label for="wps-link-password">
					<?php esc_html_e( 'Optional Password:', 'plugin-wp-support-thisismyurl' ); ?>
				</label>
				<input type="password" id="wps-link-password" class="regular-text" placeholder="<?php esc_attr_e( 'Leave empty for no password', 'plugin-wp-support-thisismyurl' ); ?>" />
			</div>

			<button type="button" class="button button-secondary" id="wps-create-link">
				<span class="dashicons dashicons-admin-links"></span>
				<?php esc_html_e( 'Create Shareable Link', 'plugin-wp-support-thisismyurl' ); ?>
			</button>

			<div class="wps-link-result" style="display: none;">
				<div class="wps-link-url">
					<input type="text" readonly class="regular-text" id="wps-shareable-url" />
					<button type="button" class="button" id="wps-copy-link">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Copy', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
				</div>
				<p class="wps-link-expires">
					<?php esc_html_e( 'Expires:', 'plugin-wp-support-thisismyurl' ); ?>
					<strong id="wps-link-expires-at"></strong>
				</p>
			</div>
		</div>
	</div>

	<div class="wps-report-output" style="display: none;">
		<h2><?php esc_html_e( 'Report Output', 'plugin-wp-support-thisismyurl' ); ?></h2>
		
		<div class="wps-report-toolbar">
			<button type="button" class="button" id="wps-copy-report">
				<span class="dashicons dashicons-clipboard"></span>
				<?php esc_html_e( 'Copy to Clipboard', 'plugin-wp-support-thisismyurl' ); ?>
			</button>
			
			<button type="button" class="button" id="wps-download-report">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Download', 'plugin-wp-support-thisismyurl' ); ?>
			</button>
		</div>

		<textarea readonly id="wps-report-content" rows="20"></textarea>
	</div>

	<div class="wps-report-info">
		<h2><?php esc_html_e( 'What\'s Included?', 'plugin-wp-support-thisismyurl' ); ?></h2>
		
		<ul class="wps-info-list">
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'WordPress, PHP, and MySQL versions', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Active theme with version', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'All plugins (active/inactive) with versions', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Server configuration (memory, execution time, upload limits)', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Error log (last 100 lines, sanitized)', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Database size and largest tables', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'File permissions (wp-content, uploads)', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cron status', 'plugin-wp-support-thisismyurl' ); ?></li>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Rewrite rules status', 'plugin-wp-support-thisismyurl' ); ?></li>
			<?php if ( is_multisite() ) : ?>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multisite configuration', 'plugin-wp-support-thisismyurl' ); ?></li>
			<?php endif; ?>
			<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'wp-config.php constants (sanitized)', 'plugin-wp-support-thisismyurl' ); ?></li>
		</ul>

		<div class="wps-privacy-notice">
			<h3><?php esc_html_e( 'Privacy & Security', 'plugin-wp-support-thisismyurl' ); ?></h3>
			<ul>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Reports generated locally only', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Sensitive data automatically sanitized (passwords, API keys, salts)', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Shareable links expire after 7 days', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Admin-only access', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ul>
		</div>
	</div>
</div>
