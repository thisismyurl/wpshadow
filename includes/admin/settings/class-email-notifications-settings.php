<?php
/**
 * Email Notifications Settings Page
 *
 * Admin page for configuring email notification preferences
 *
 * @since 1.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Notifications_Settings Class
 *
 * Renders the email notifications settings page
 *
 * @since 1.6093.1200
 */
class Email_Notifications_Settings {

	/**
	 * Initialize settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'wpshadow_register_admin_pages', array( __CLASS__, 'register_settings_section' ) );
	}

	/**
	 * Register settings section
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_settings_section() {
		// This is handled by the Settings_Registry in the notifications class
		// This page just renders the UI
	}

	/**
	 * Render email notifications settings form
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_form() {
		$enabled = get_option( 'wpshadow_email_notifications_enabled', false );
		$email = get_option( 'wpshadow_email_notifications_email', get_option( 'admin_email' ) );
		$threshold = get_option( 'wpshadow_email_notifications_threshold', 'high' );
		$digest_mode = get_option( 'wpshadow_email_notifications_digest_enabled', false );
		$nonce = wp_create_nonce( 'wpshadow_email_notifications_nonce' );
		?>
		<div class="wpshadow-settings-section">
			<h2><?php esc_html_e( 'Email Notifications', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Receive email alerts when urgent items are detected on your site.', 'wpshadow' ); ?></p>

			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_email_notifications_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="email-notifications-enabled">
								<?php esc_html_e( 'Enable Email Notifications', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="checkbox" 
								id="email-notifications-enabled" 
								name="wpshadow_email_notifications_enabled"
								value="1"
								<?php checked( $enabled ); ?>
								aria-describedby="email-notifications-enabled-description"
							/>
							<p id="email-notifications-enabled-description" class="description">
								<?php esc_html_e( 'Check this box to receive email notifications for important findings.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="email-notifications-email">
								<?php esc_html_e( 'Notification Email Address', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="email" 
								id="email-notifications-email" 
								name="wpshadow_email_notifications_email"
								value="<?php echo esc_attr( $email ); ?>"
								class="regular-text"
								aria-describedby="email-notifications-email-description"
							/>
							<p id="email-notifications-email-description" class="description">
								<?php esc_html_e( 'Email address where notifications should be sent.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="email-notifications-threshold">
								<?php esc_html_e( 'Minimum Severity Level', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select 
								id="email-notifications-threshold"
								name="wpshadow_email_notifications_threshold"
								aria-describedby="email-notifications-threshold-description"
							>
								<option value="critical" <?php selected( $threshold, 'critical' ); ?>>
							<?php esc_html_e( 'Most Urgent Items Only', 'wpshadow' ); ?>
						</option>
						<option value="high" <?php selected( $threshold, 'high' ); ?>>
							<?php esc_html_e( 'High Priority & Urgent Items', 'wpshadow' ); ?>
								</option>
								<option value="medium" <?php selected( $threshold, 'medium' ); ?>>
									<?php esc_html_e( 'Medium & Above', 'wpshadow' ); ?>
								</option>
								<option value="low" <?php selected( $threshold, 'low' ); ?>>
									<?php esc_html_e( 'All Issues', 'wpshadow' ); ?>
								</option>
							</select>
							<p id="email-notifications-threshold-description" class="description">
								<?php esc_html_e( 'Only send notifications for issues of this severity level or higher.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="email-notifications-digest">
								<?php esc_html_e( 'Digest Mode (Daily Summary)', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="checkbox" 
								id="email-notifications-digest"
								name="wpshadow_email_notifications_digest_enabled"
								value="1"
								<?php checked( $digest_mode ); ?>
								aria-describedby="email-notifications-digest-description"
							/>
							<p id="email-notifications-digest-description" class="description">
								<?php esc_html_e( 'Instead of individual emails, receive one daily summary of all findings.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
