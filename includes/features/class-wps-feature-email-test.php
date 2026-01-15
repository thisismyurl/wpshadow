<?php
/**
 * Feature: Email Test
 *
 * Provides email functionality testing and troubleshooting tools:
 * - Send test emails to verify SMTP configuration
 * - View email logs and delivery status
 * - Diagnose common email problems
 * - Test different email configurations
 *
 * @package WPS\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Email_Test
 *
 * Email testing and diagnostics.
 */
final class WPS_Feature_Email_Test extends WPS_Abstract_Feature {

	/**
	 * Option key for email logs.
	 */
	private const LOG_KEY = 'wps_email_test_log';

	/**
	 * Maximum number of logs to keep.
	 */
	private const MAX_LOGS = 50;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'email-test',
				'name'               => __( 'Email Test & Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Test email delivery, diagnose SMTP issues, and view email logs to ensure notifications work correctly', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'debugging',
				'widget_label'       => __( 'Debugging & Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Tools for diagnosing and resolving site issues', 'plugin-wp-support-thisismyurl' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-email',
				'category'           => 'debugging',
				'priority'           => 15,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 15,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_send_test_email', array( $this, 'ajax_send_test_email' ) );
		add_action( 'wp_ajax_wps_clear_email_logs', array( $this, 'ajax_clear_logs' ) );

		// Log all emails if enabled in settings.
		if ( $this->get_setting( 'log_all_emails', false ) ) {
			add_action( 'wp_mail_succeeded', array( $this, 'log_email_success' ) );
			add_action( 'wp_mail_failed', array( $this, 'log_email_failure' ) );
		}
	}

	/**
	 * Add admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Email Test', 'plugin-wp-support-thisismyurl' ),
			__( 'Email Test', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wp-support-email-test',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Log successful email.
	 *
	 * @param array $mail_data Email data from wp_mail.
	 * @return void
	 */
	public function log_email_success( $mail_data ): void {
		$this->add_log_entry(
			array(
				'status'  => 'success',
				'to'      => is_array( $mail_data['to'] ) ? implode( ', ', $mail_data['to'] ) : $mail_data['to'],
				'subject' => $mail_data['subject'] ?? '',
				'time'    => time(),
			)
		);
	}

	/**
	 * Log failed email.
	 *
	 * @param \WP_Error $error Error object.
	 * @return void
	 */
	public function log_email_failure( $error ): void {
		if ( ! $error instanceof \WP_Error ) {
			return;
		}

		$mail_data = $error->get_error_data();

		$this->add_log_entry(
			array(
				'status'  => 'failed',
				'to'      => is_array( $mail_data['to'] ?? '' ) ? implode( ', ', $mail_data['to'] ) : ( $mail_data['to'] ?? '' ),
				'subject' => $mail_data['subject'] ?? '',
				'error'   => $error->get_error_message(),
				'time'    => time(),
			)
		);
	}

	/**
	 * Add log entry.
	 *
	 * @param array $entry Log entry data.
	 * @return void
	 */
	private function add_log_entry( array $entry ): void {
		$logs = get_option( self::LOG_KEY, array() );
		if ( ! is_array( $logs ) ) {
			$logs = array();
		}

		// Add new entry at the beginning.
		array_unshift( $logs, $entry );

		// Keep only the most recent entries.
		$logs = array_slice( $logs, 0, self::MAX_LOGS );

		update_option( self::LOG_KEY, $logs, false );
	}

	/**
	 * Get email logs.
	 *
	 * @return array Email logs.
	 */
	private function get_logs(): array {
		$logs = get_option( self::LOG_KEY, array() );
		return is_array( $logs ) ? $logs : array();
	}

	/**
	 * AJAX: Send test email.
	 *
	 * @return void
	 */
	public function ajax_send_test_email(): void {
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_email_test' );

		$to      = \WPS\CoreSupport\wps_get_post_email( 'to' );
		$subject = \WPS\CoreSupport\wps_get_post_text( 'subject', __( 'WP Support Test Email', 'plugin-wp-support-thisismyurl' ) );
		$message = \WPS\CoreSupport\wps_get_post_html( 'message', $this->get_default_test_message() );

		if ( empty( $to ) || ! is_email( $to ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$result = wp_mail( $to, $subject, $message, $headers );

		if ( $result ) {
			// Log successful test.
			$this->add_log_entry(
				array(
					'status'  => 'success',
					'to'      => $to,
					'subject' => $subject,
					'type'    => 'test',
					'time'    => time(),
				)
			);

			wp_send_json_success(
				array(
					'message' => __( 'Test email sent successfully!', 'plugin-wp-support-thisismyurl' ),
					'details' => $this->get_email_diagnostics(),
				)
			);
		} else {
			// Log failed test.
			$this->add_log_entry(
				array(
					'status'  => 'failed',
					'to'      => $to,
					'subject' => $subject,
					'type'    => 'test',
					'error'   => __( 'wp_mail() returned false', 'plugin-wp-support-thisismyurl' ),
					'time'    => time(),
				)
			);

			wp_send_json_error(
				array(
					'message' => __( 'Failed to send test email', 'plugin-wp-support-thisismyurl' ),
					'details' => $this->get_email_diagnostics(),
				)
			);
		}
	}

	/**
	 * AJAX: Clear email logs.
	 *
	 * @return void
	 */
	public function ajax_clear_logs(): void {
		\WPS\CoreSupport\wps_verify_ajax_request( 'wps_email_test' );

		delete_option( self::LOG_KEY );

		wp_send_json_success( array( 'message' => __( 'Email logs cleared', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * Get default test message.
	 *
	 * @return string HTML message.
	 */
	private function get_default_test_message(): string {
		$site_name = get_bloginfo( 'name' );
		$site_url  = get_bloginfo( 'url' );
		$time      = current_time( 'mysql' );

		return sprintf(
			'<html><body>
			<h2>%s</h2>
			<p>%s</p>
			<ul>
				<li><strong>%s:</strong> %s</li>
				<li><strong>%s:</strong> <a href="%s">%s</a></li>
				<li><strong>%s:</strong> %s</li>
				<li><strong>%s:</strong> %s</li>
			</ul>
			<p>%s</p>
			</body></html>',
			esc_html__( 'Test Email from WP Support', 'plugin-wp-support-thisismyurl' ),
			esc_html__( 'This is a test email sent from your WordPress site to verify email functionality.', 'plugin-wp-support-thisismyurl' ),
			esc_html__( 'Site Name', 'plugin-wp-support-thisismyurl' ),
			esc_html( $site_name ),
			esc_html__( 'Site URL', 'plugin-wp-support-thisismyurl' ),
			esc_url( $site_url ),
			esc_url( $site_url ),
			esc_html__( 'Sent At', 'plugin-wp-support-thisismyurl' ),
			esc_html( $time ),
			esc_html__( 'WordPress Version', 'plugin-wp-support-thisismyurl' ),
			esc_html( get_bloginfo( 'version' ) ),
			esc_html__( 'If you received this email, your WordPress email functionality is working correctly!', 'plugin-wp-support-thisismyurl' )
		);
	}

	/**
	 * Get email diagnostics.
	 *
	 * @return array Diagnostic information.
	 */
	private function get_email_diagnostics(): array {
		global $phpmailer;

		$diagnostics = array(
			'php_mail_available' => function_exists( 'mail' ),
			'from_email'         => get_option( 'admin_email' ),
			'from_name'          => get_bloginfo( 'name' ),
		);

		// Get PHPMailer info if available.
		if ( isset( $phpmailer ) && is_object( $phpmailer ) ) {
			$diagnostics['mailer'] = $phpmailer->Mailer;
			if ( 'smtp' === $phpmailer->Mailer ) {
				$diagnostics['smtp_host'] = $phpmailer->Host;
				$diagnostics['smtp_port'] = $phpmailer->Port;
				$diagnostics['smtp_auth'] = $phpmailer->SMTPAuth;
			}
		}

		return $diagnostics;
	}

	/**
	 * Render email test page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$logs        = $this->get_logs();
		$diagnostics = $this->get_email_diagnostics();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Email Test & Diagnostics', 'plugin-wp-support-thisismyurl' ); ?></h1>

			<div class="card">
				<h2><?php esc_html_e( 'Send Test Email', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<form id="wps-email-test-form">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="wps-email-to"><?php esc_html_e( 'Send To', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input
									type="email"
									id="wps-email-to"
									name="to"
									class="regular-text"
									value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"
									required
								/>
								<p class="description"><?php esc_html_e( 'Email address to send test message to', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wps-email-subject"><?php esc_html_e( 'Subject', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<input
									type="text"
									id="wps-email-subject"
									name="subject"
									class="regular-text"
									value="<?php esc_attr_e( 'WP Support Test Email', 'plugin-wp-support-thisismyurl' ); ?>"
									required
								/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wps-email-message"><?php esc_html_e( 'Message', 'plugin-wp-support-thisismyurl' ); ?></label>
							</th>
							<td>
								<textarea
									id="wps-email-message"
									name="message"
									rows="10"
									class="large-text code"
								><?php echo esc_textarea( $this->get_default_test_message() ); ?></textarea>
								<p class="description"><?php esc_html_e( 'HTML is allowed', 'plugin-wp-support-thisismyurl' ); ?></p>
							</td>
						</tr>
					</table>

					<p>
						<button type="submit" class="button button-primary" id="wps-send-test-email">
							<?php esc_html_e( 'Send Test Email', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</p>
				</form>

				<div id="wps-email-result" style="margin-top: 20px;"></div>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Email Configuration', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th style="width: 30%;"><?php esc_html_e( 'PHP mail() Available', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td>
								<?php if ( $diagnostics['php_mail_available'] ) : ?>
									<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
									<?php esc_html_e( 'Yes', 'plugin-wp-support-thisismyurl' ); ?>
								<?php else : ?>
									<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
									<?php esc_html_e( 'No', 'plugin-wp-support-thisismyurl' ); ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'From Email', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td><code><?php echo esc_html( $diagnostics['from_email'] ); ?></code></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'From Name', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td><?php echo esc_html( $diagnostics['from_name'] ); ?></td>
						</tr>
						<?php if ( isset( $diagnostics['mailer'] ) ) : ?>
							<tr>
								<th><?php esc_html_e( 'Mailer', 'plugin-wp-support-thisismyurl' ); ?></th>
								<td><code><?php echo esc_html( strtoupper( $diagnostics['mailer'] ) ); ?></code></td>
							</tr>
							<?php if ( 'smtp' === $diagnostics['mailer'] ) : ?>
								<tr>
									<th><?php esc_html_e( 'SMTP Host', 'plugin-wp-support-thisismyurl' ); ?></th>
									<td><code><?php echo esc_html( $diagnostics['smtp_host'] ?? 'N/A' ); ?></code></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'SMTP Port', 'plugin-wp-support-thisismyurl' ); ?></th>
									<td><code><?php echo esc_html( $diagnostics['smtp_port'] ?? 'N/A' ); ?></code></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'SMTP Auth', 'plugin-wp-support-thisismyurl' ); ?></th>
									<td><?php echo $diagnostics['smtp_auth'] ? esc_html__( 'Enabled', 'plugin-wp-support-thisismyurl' ) : esc_html__( 'Disabled', 'plugin-wp-support-thisismyurl' ); ?></td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ( ! empty( $logs ) ) : ?>
				<div class="card">
					<h2>
						<?php esc_html_e( 'Email Log', 'plugin-wp-support-thisismyurl' ); ?>
						<button type="button" id="wps-clear-email-logs" class="button button-small" style="float: right;">
							<?php esc_html_e( 'Clear Logs', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 80px;"><?php esc_html_e( 'Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'To', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php esc_html_e( 'Subject', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th style="width: 150px;"><?php esc_html_e( 'Time', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $logs as $log ) : ?>
								<tr>
									<td>
										<?php if ( 'success' === $log['status'] ) : ?>
											<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
											<?php esc_html_e( 'Success', 'plugin-wp-support-thisismyurl' ); ?>
										<?php else : ?>
											<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
											<?php esc_html_e( 'Failed', 'plugin-wp-support-thisismyurl' ); ?>
										<?php endif; ?>
										<?php if ( isset( $log['type'] ) && 'test' === $log['type'] ) : ?>
											<br><small><em><?php esc_html_e( '(Test)', 'plugin-wp-support-thisismyurl' ); ?></em></small>
										<?php endif; ?>
									</td>
									<td><code><?php echo esc_html( $log['to'] ); ?></code></td>
									<td><?php echo esc_html( $log['subject'] ); ?></td>
									<td>
										<?php
										echo esc_html(
											wp_date(
												get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
												$log['time']
											)
										);
										?>
										<?php if ( isset( $log['error'] ) ) : ?>
											<br><small style="color: #dc3232;"><?php echo esc_html( $log['error'] ); ?></small>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			const nonce = '<?php echo esc_js( wp_create_nonce( 'wps_email_test' ) ); ?>';

			// Send test email.
			$('#wps-email-test-form').on('submit', function(e) {
				e.preventDefault();

				const $form = $(this);
				const $button = $('#wps-send-test-email');
				const $result = $('#wps-email-result');

				$button.prop('disabled', true).text('<?php echo esc_js( __( 'Sending...', 'plugin-wp-support-thisismyurl' ) ); ?>');
				$result.html('');

				$.post(ajaxurl, {
					action: 'wps_send_test_email',
					nonce: nonce,
					to: $('#wps-email-to').val(),
					subject: $('#wps-email-subject').val(),
					message: $('#wps-email-message').val()
				}, function(response) {
					if (response.success) {
						$result.html(
							'<div class="notice notice-success"><p><strong>' + response.data.message + '</strong></p></div>'
						);
						// Reload to show new log entry.
						setTimeout(function() {
							window.location.reload();
						}, 2000);
					} else {
						$result.html(
							'<div class="notice notice-error"><p><strong>' + response.data.message + '</strong></p></div>'
						);
					}
				}).always(function() {
					$button.prop('disabled', false).text('<?php echo esc_js( __( 'Send Test Email', 'plugin-wp-support-thisismyurl' ) ); ?>');
				});
			});

			// Clear logs.
			$('#wps-clear-email-logs').on('click', function(e) {
				e.preventDefault();

				if (!confirm('<?php echo esc_js( __( 'Clear all email logs?', 'plugin-wp-support-thisismyurl' ) ); ?>')) {
					return;
				}

				const $button = $(this);
				$button.prop('disabled', true);

				$.post(ajaxurl, {
					action: 'wps_clear_email_logs',
					nonce: nonce
				}, function(response) {
					if (response.success) {
						window.location.reload();
					}
				});
			});
		});
		</script>
		<?php
	}
}
