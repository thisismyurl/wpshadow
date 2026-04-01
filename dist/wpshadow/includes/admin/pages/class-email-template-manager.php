<?php
declare(strict_types=1);

namespace WPShadow\Admin\Pages;
use WPShadow\Core\Form_Param_Helper;

/**
 * Email Template Manager
 *
 * Manages email template customization for WPShadow reports and notifications.
 * Philosophy: Free as Possible (#2) - All templates editable locally, no paywalls
 * Philosophy: Show Value (#9) - Track email customization usage
 *
 * @since 0.6093.1200
 * @package WPShadow
 */
class Email_Template_Manager {

	/**
	 * Option key for email templates storage
	 */
	const OPTION_KEY = 'wpshadow_email_templates';

	/**
	 * Get default email templates
	 *
	 * @return array Default templates with HTML and plain text versions
	 */
	public static function get_default_templates() {
		return array(
			'report_executive'      => array(
				'label'       => __( 'Executive Report Email', 'wpshadow' ),
				'description' => __( 'Professional format for management review', 'wpshadow' ),
				'html'        => self::get_default_html_executive(),
				'text'        => self::get_default_text_executive(),
			),
			'report_detailed'       => array(
				'label'       => __( 'Detailed Report Email', 'wpshadow' ),
				'description' => __( 'Technical details for site administrators', 'wpshadow' ),
				'html'        => self::get_default_html_detailed(),
				'text'        => self::get_default_text_detailed(),
			),
			'alert_critical'        => array(
				'label'       => __( 'Urgent Alert Email', 'wpshadow' ),
				'description' => __( 'Friendly heads-up for items that need attention soon', 'wpshadow' ),
				'html'        => self::get_default_html_alert(),
				'text'        => self::get_default_text_alert(),
			),
			'notification_workflow' => array(
				'label'       => __( 'Workflow Completion Email', 'wpshadow' ),
				'description' => __( 'Confirm workflow actions completed', 'wpshadow' ),
				'html'        => self::get_default_html_workflow(),
				'text'        => self::get_default_text_workflow(),
			),
		);
	}

	/**
	 * Get all email templates (custom + defaults)
	 *
	 * @return array All templates merged with defaults
	 */
	public static function get_all_templates() {
		$defaults = self::get_default_templates();
		$custom   = get_option( self::OPTION_KEY, array() );

		// Merge custom templates with defaults
		foreach ( $defaults as $key => $template ) {
			if ( isset( $custom[ $key ] ) ) {
				$defaults[ $key ]['html']   = $custom[ $key ]['html'] ?? $template['html'];
				$defaults[ $key ]['text']   = $custom[ $key ]['text'] ?? $template['text'];
				$defaults[ $key ]['custom'] = true;
			}
		}

		return $defaults;
	}

	/**
	 * Get specific template by key
	 *
	 * @param string $key Template key (e.g., 'report_executive')
	 * @param string $format Format to retrieve ('html' or 'text')
	 * @return string Template content, or empty if not found
	 */
	public static function get_template( $key, $format = 'html' ) {
		$templates = self::get_all_templates();

		if ( ! isset( $templates[ $key ] ) ) {
			return '';
		}

		return $templates[ $key ][ $format ] ?? '';
	}

	/**
	 * Save custom email template
	 *
	 * @param string $key Template key
	 * @param string $html HTML template content
	 * @param string $text Plain text template content
	 * @return bool Success status
	 */
	public static function save_template( $key, $html, $text ) {
		if ( empty( $key ) ) {
			return false;
		}

		// Sanitize HTML to allow safe markup
		$html = wp_kses_post( $html );
		$text = sanitize_textarea_field( $text );

		// Get existing templates
		$templates = get_option( self::OPTION_KEY, array() );

		// Update template
		$templates[ $key ] = array(
			'html'    => $html,
			'text'    => $text,
			'updated' => current_time( 'timestamp' ),
		);

		// Save templates
		$result = update_option( self::OPTION_KEY, $templates );

		// Log activity (Philosophy #9: Show Value)
		if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'email_template_customized',
				sprintf( 'Email template updated: %s', $key ),
				'',
				array( 'template_key' => $key )
			);
		}

		return $result;
	}

	/**
	 * Reset template to default
	 *
	 * @param string $key Template key
	 * @return bool Success status
	 */
	public static function reset_template( $key ) {
		if ( empty( $key ) ) {
			return false;
		}

		$templates = get_option( self::OPTION_KEY, array() );

		if ( isset( $templates[ $key ] ) ) {
			unset( $templates[ $key ] );
			$result = update_option( self::OPTION_KEY, $templates );

			// Log activity
			if ( $result && class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'email_template_reset',
					sprintf( 'Email template reset: %s', $key ),
					'',
					array( 'template_key' => $key )
				);
			}

			return $result;
		}

		return true;
	}

	/**
	 * Render email template editor UI
	 *
	 * Philosophy: Ridiculously good (#7) - Intuitive editor with preview
	 *
	 * @return void
	 */
	public static function render_template_editor() {
		$templates         = self::get_all_templates();
		$selected_template = Form_Param_Helper::get( 'template', 'key', 'report_executive' );

		if ( ! isset( $templates[ $selected_template ] ) ) {
			$selected_template = 'report_executive';
		}

		$template = $templates[ $selected_template ];

		wp_enqueue_style(
			'wpshadow-email-template-manager',
			WPSHADOW_URL . 'assets/css/email-template-manager.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-email-template-manager',
			WPSHADOW_URL . 'assets/js/email-template-manager.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-email-template-manager',
			'wpshadowEmailTemplateManager',
			array(
				'nonce'    => wp_create_nonce( 'wpshadow_email_template_nonce' ),
				'strings'  => array(
					'saving'            => __( 'Saving...', 'wpshadow' ),
					'save_template'     => __( 'Save Template', 'wpshadow' ),
					'error_saving'      => __( 'Error saving template', 'wpshadow' ),
					'network_error'     => __( 'Network error', 'wpshadow' ),
					'preview_title'     => __( 'WPShadow Report', 'wpshadow' ),
					'preview_content'   => __( 'Your site health report is ready.', 'wpshadow' ),
					'preview_footer'    => __( 'Powered by WPShadow', 'wpshadow' ),
					'reset_confirm'     => __( 'Reset this template to default?', 'wpshadow' ),
				),
				'template_base_url' => add_query_arg( 'template', '', admin_url( 'admin.php?page=wpshadow-settings&tab=email' ) ),
			)
		);
		?>
		<div class="wps-email-container">
			<!-- Template Selector -->
			<?php
			wpshadow_render_card(
				array(
					'title' => __( 'Select Email Template', 'wpshadow' ),
					'body'  => function() use ( $templates, $selected_template ) {
						?>
						<div class="wps-grid wps-grid-auto-250 wps-gap-3">
							<?php foreach ( $templates as $key => $tmpl ) : ?>
							<label class="wps-email-template-option <?php echo isset( $tmpl['custom'] ) ? 'wps-email-template-option-custom' : 'wps-email-template-option-default'; ?>">
								<input type="radio" name="template" value="<?php echo esc_attr( $key ); ?>" <?php checked( $selected_template, $key ); ?> class="wps-email-template-radio" />
								<div>
									<strong class="wps-email-template-label"><?php echo esc_html( $tmpl['label'] ); ?></strong>
									<?php if ( isset( $tmpl['custom'] ) ) : ?>
									<span class="wps-block wps-email-template-customized">● <?php esc_html_e( 'Customized', 'wpshadow' ); ?></span>
									<?php endif; ?>
									<p class="wps-m-4"><?php echo esc_html( $tmpl['description'] ); ?></p>
								</div>
							</label>
							<?php endforeach; ?>
						</div>
						<?php
					},
				)
			);
			?>

			<!-- Template Editor -->
			<div class="wps-grid wps-grid-auto-320 wps-gap-4">
				<!-- Edit Section -->
				<?php
				wpshadow_render_card(
					array(
						'title' => __( 'Edit Template', 'wpshadow' ),
						'body'  => function() use ( $selected_template, $template ) {
							?>
							<form id="wpshadow-email-template-form" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
								<?php wp_nonce_field( 'wpshadow_email_template_nonce' ); ?>
								<input type="hidden" name="action" value="wpshadow_save_email_template" />
								<input type="hidden" name="template_key" value="<?php echo esc_attr( $selected_template ); ?>" />

								<!-- HTML Template -->
								<label class="wps-block">
									<strong><?php esc_html_e( 'HTML Template', 'wpshadow' ); ?></strong>
									<p class="wps-m-2">
										<?php esc_html_e( 'Use {title}, {content}, {footer} placeholders', 'wpshadow' ); ?>
									</p>
								</label>
								<textarea name="template_html" class="wps-email-textarea wps-textarea"><?php echo esc_textarea( $template['html'] ); ?></textarea>

								<!-- Plain Text Template -->
								<label class="wps-block-m-15">
									<strong><?php esc_html_e( 'Plain Text Template', 'wpshadow' ); ?></strong>
									<p class="wps-m-2">
										<?php esc_html_e( 'Fallback for email clients that don\'t support HTML', 'wpshadow' ); ?>
									</p>
								</label>
								<textarea name="template_text" class="wps-email-textarea wps-textarea"><?php echo esc_textarea( $template['text'] ); ?></textarea>

								<!-- Actions -->
								<div class="wps-flex wps-gap-2 wps-email-actions">
									<button type="submit" class="wps-btn wps-btn-primary">
										<?php esc_html_e( 'Save Template', 'wpshadow' ); ?>
									</button>
									<button type="button" class="wps-btn wps-btn-secondary wps-reset-email-template" data-template-key="<?php echo esc_attr( $selected_template ); ?>">
										<?php esc_html_e( 'Reset to Default', 'wpshadow' ); ?>
									</button>
								</div>
								<p id="wpshadow-template-status" class="wps-email-status"></p>
							</form>
							<?php
						},
					)
				);
				?>

				<!-- Preview Section -->
				<?php
				wpshadow_render_card(
					array(
						'title' => __( 'Preview', 'wpshadow' ),
						'body'  => function() {
							?>
							<div class="wps-p-15-rounded-4">
								<div id="wpshadow-template-preview" class="wps-email-preview">
									<p class="wps-email-preview-placeholder">
										<?php esc_html_e( 'Live preview of your template will appear here', 'wpshadow' ); ?>
									</p>
								</div>
							</div>
							<p class="wps-email-help-text">
								<?php esc_html_e( 'This preview uses sample data. Actual emails will contain your real data.', 'wpshadow' ); ?>
							</p>
							<?php
						},
					)
				);
				?>
			</div>
		</div>

		<?php
	}

	/**
	 * Default HTML template for Executive Report
	 *
	 * @return string HTML template
	 */
	private static function get_default_html_executive() {
		return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.header { background: linear-gradient(135deg, #0073aa 0%, #005a87 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
		.content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
		.footer { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; font-size: 12px; color: #666; }
		.metric { display: inline-block; margin: 10px 15px 10px 0; }
		.metric-value { font-size: 24px; font-weight: bold; color: #0073aa; }
		.metric-label { font-size: 12px; color: #666; }
		.button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>{title}</h2>
			<p>Performance summary for your WordPress site</p>
		</div>
		<div class="content">
			<p>Hello,</p>
			<p>{content}</p>
			<div class="wps-m-20-p-20-rounded-4">
				<div class="metric">
					<div class="metric-value">95%</div>
					<div class="metric-label">Site Health</div>
				</div>
				<div class="metric">
					<div class="metric-value">2</div>
					<div class="metric-label">Issues Found</div>
				</div>
				<div class="metric">
					<div class="metric-value">$240</div>
					<div class="metric-label">Value Delivered</div>
				</div>
			</div>
			<p><a href="{dashboard_url}" class="button">View Full Report</a></p>
		</div>
		<div class="footer">
			<p>{footer}</p>
			<p class="wps-m-10">© <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> - All rights reserved</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	/**
	 * Default plain text template for Executive Report
	 *
	 * @return string Plain text template
	 */
	private static function get_default_text_executive() {
		return <<<'TEXT'
{title}
Performance Summary for Your WordPress Site

Hello,

{content}

Site Health: 95%
Issues Found: 2
Value Delivered: $240

View the full report:
{dashboard_url}

{footer}

© <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> - All rights reserved
TEXT;
	}

	/**
	 * Default HTML template for Detailed Report
	 *
	 * @return string HTML template
	 */
	private static function get_default_html_detailed() {
		return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: monospace; color: #333; font-size: 12px; }
		.container { max-width: 800px; margin: 0 auto; padding: 20px; }
		.header { background: #222; color: white; padding: 15px; }
		.section { margin: 15px 0; padding: 15px; background: #f5f5f5; border-left: 3px solid #0073aa; }
		.issue { margin: 8px 0; padding: 8px; background: white; border-left: 2px solid #ff9800; }
		.issue.critical { border-left-color: #f44336; }
		table { width: 100%; border-collapse: collapse; margin: 10px 0; }
		th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
		th { background: #f0f0f0; font-weight: bold; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>{title}</h2>
			<p>Generated: {date}</p>
		</div>
		<div class="section">
			<h3>Executive Summary</h3>
			<p>{content}</p>
		</div>
		<div class="section">
			<h3>Issues Detected</h3>
			<table>
				<thead>
					<tr>
						<th>Issue</th>
						<th>Category</th>
						<th>Severity</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Outdated WordPress Core</td>
						<td>Security</td>
						<td>High</td>
						<td>Pending</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="section">
			<h3>Recommendations</h3>
			<p>{recommendations}</p>
		</div>
		<div class="footer" class="wps-p-15">
			<p>{footer}</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	/**
	 * Default plain text template for Detailed Report
	 *
	 * @return string Plain text template
	 */
	private static function get_default_text_detailed() {
		return <<<'TEXT'
{title}
Generated: {date}

EXECUTIVE SUMMARY
{content}

ISSUES DETECTED
See attached report for detailed list of issues.

RECOMMENDATIONS
{recommendations}

{footer}
TEXT;
	}

	/**
	 * Default HTML template for Critical Alert
	 *
	 * @return string HTML template
	 */
	private static function get_default_html_alert() {
		return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.alert { background: #ffebee; border: 2px solid #f44336; border-radius: 8px; padding: 20px; }
		.alert h2 { color: #c62828; margin-top: 0; }
		.icon { font-size: 32px; margin-bottom: 10px; }
		.action-button { display: inline-block; padding: 12px 24px; background: #f44336; color: white; text-decoration: none; border-radius: 4px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="alert">
			<div class="icon">⚠️</div>
			<h2>{title}</h2>
			<p class="wps-email-template-content">{content}</p>
			<p><strong>Action Required:</strong> This issue poses a security or stability risk.</p>
			<p class="wps-email-actions">
				<a href="{dashboard_url}" class="action-button">Review Issue Now</a>
			</p>
		</div>
		<div class="wps-p-15-rounded-4">
			<p class="wps-m-0">{footer}</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	/**
	 * Default plain text template for Critical Alert
	 *
	 * @return string Plain text template
	 */
	private static function get_default_text_alert() {
		return <<<'TEXT'
IMPORTANT UPDATE: {title}

⚠️ Your WordPress site needs attention.

{content}

Here's what this means: This could affect your site's security or stability.

Take a look when you can:
{dashboard_url}

{footer}
TEXT;
	}

	/**
	 * Default HTML template for Workflow Completion
	 *
	 * @return string HTML template
	 */
	private static function get_default_html_workflow() {
		return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.success { background: #e8f5e9; border: 1px solid #66bb6a; border-radius: 8px; padding: 20px; }
		.success h2 { color: #2e7d32; margin-top: 0; }
		.checkmark { font-size: 48px; margin-bottom: 15px; }
		.details { background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin: 15px 0; }
		.detail-item { display: flex; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
		.detail-label { font-weight: bold; color: #666; width: 120px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="success">
			<div class="checkmark">✓</div>
			<h2>{title}</h2>
			<p>{content}</p>
			<div class="details">
				<div class="detail-item">
					<div class="detail-label">Status:</div>
					<div>Completed Successfully</div>
				</div>
				<div class="detail-item">
					<div class="detail-label">Time Saved:</div>
					<div>~5 minutes</div>
				</div>
				<div class="detail-item">
					<div class="detail-label">Completed At:</div>
					<div>{timestamp}</div>
				</div>
			</div>
		</div>
		<div class="wps-p-15-rounded-4">
			<p class="wps-m-0">{footer}</p>
		</div>
	</div>
</body>
</html>
HTML;
	}

	/**
	 * Default plain text template for Workflow Completion
	 *
	 * @return string Plain text template
	 */
	private static function get_default_text_workflow() {
		return <<<'TEXT'
✓ {title}

{content}

Status: Completed Successfully
Time Saved: ~5 minutes
Completed At: {timestamp}

{footer}
TEXT;
	}
}
