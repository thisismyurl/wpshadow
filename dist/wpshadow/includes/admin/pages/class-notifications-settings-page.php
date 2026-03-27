<?php
/**
 * Notifications Settings Page
 *
 * Configures email alerts, notification preferences, and notification rules.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications Settings Page
 *
 * @since 1.6093.1200
 */
class Notifications_Settings_Page {

	/**
	 * Render the notifications settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Notifications', 'wpshadow' ),
				__( 'Configure email alerts and notification preferences.', 'wpshadow' ),
				'dashicons-email'
			);
			?>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Email Notification Basics -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Email Notifications', 'wpshadow' ),
						'description' => __( 'Control when and how WPShadow sends you email alerts.', 'wpshadow' ),
						'icon'        => 'dashicons-email-alt',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_notifications_enabled',
									'name'        => 'wpshadow_notifications_enabled',
									'label'       => __( 'Enable Email Notifications', 'wpshadow' ),
									'helper_text' => __( 'Get email alerts when WPShadow finds urgent items or completes scheduled checks.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_notifications_enabled', true ),
								)
							);
							?>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_notification_severity" class="wps-form-label">
									<?php esc_html_e( 'Email Me For', 'wpshadow' ); ?>
								</label>
								<select 
									id="wpshadow_notification_severity" 
									name="wpshadow_notification_severity"
									class="wps-input"
								>
									<option value="critical" <?php selected( get_option( 'wpshadow_notification_severity', 'critical' ), 'critical' ); ?>>
										<?php esc_html_e( 'Most urgent items only 🚨 (like smoke alarms)', 'wpshadow' ); ?>
									</option>
									<option value="high" <?php selected( get_option( 'wpshadow_notification_severity', 'critical' ), 'high' ); ?>>
										<?php esc_html_e( 'High priority & urgent items 🟠', 'wpshadow' ); ?>
									</option>
									<option value="medium" <?php selected( get_option( 'wpshadow_notification_severity', 'critical' ), 'medium' ); ?>>
										<?php esc_html_e( 'Medium & above 🟡', 'wpshadow' ); ?>
									</option>
									<option value="all" <?php selected( get_option( 'wpshadow_notification_severity', 'critical' ), 'all' ); ?>>
										<?php esc_html_e( 'All issues 🔵', 'wpshadow' ); ?>
									</option>
								</select>
								<p class="wps-form-description">
									<?php esc_html_e( 'Choose which severity levels trigger email alerts.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Notification Recipients -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Email Recipients', 'wpshadow' ),
						'description' => __( 'Control which email addresses receive WPShadow notifications.', 'wpshadow' ),
						'icon'        => 'dashicons-groups',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_notify_admin_email',
									'name'        => 'wpshadow_notify_admin_email',
									'label'       => __( 'Send to Site Admin Email', 'wpshadow' ),
									'helper_text' => sprintf( __( 'Currently: %s', 'wpshadow' ), get_option( 'admin_email' ) ),
									'checked'     => (bool) get_option( 'wpshadow_notify_admin_email', true ),
								)
							);
							?>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_additional_recipients" class="wps-form-label">
									<?php esc_html_e( 'Additional Recipients', 'wpshadow' ); ?>
								</label>
								<textarea 
									id="wpshadow_additional_recipients" 
									name="wpshadow_additional_recipients"
									class="wps-input wps-h-24"
									placeholder="dev@example.com&#10;support@example.com"
								><?php echo esc_textarea( get_option( 'wpshadow_additional_recipients', '' ) ); ?></textarea>
								<p class="wps-form-description">
									<?php esc_html_e( 'Enter one email per line. These addresses will receive all WPShadow notifications.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Notification Schedule -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Notification Schedule', 'wpshadow' ),
						'description' => __( 'Customize when notifications are sent.', 'wpshadow' ),
						'icon'        => 'dashicons-calendar',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_notification_digest',
									'name'        => 'wpshadow_notification_digest',
									'label'       => __( 'Digest Mode (Daily Summary)', 'wpshadow' ),
									'helper_text' => __( 'Instead of individual alerts, receive one daily email with all issues from the day.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_notification_digest', false ),
								)
							);
							?>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_notification_time" class="wps-form-label">
									<?php esc_html_e( 'Send Daily Digest At', 'wpshadow' ); ?>
								</label>
								<input 
									type="time" 
									id="wpshadow_notification_time" 
									name="wpshadow_notification_time" 
									value="<?php echo esc_attr( get_option( 'wpshadow_notification_time', '09:00' ) ); ?>"
									class="wps-input wps-w-32"
								/>
								<p class="wps-form-description">
									<?php echo wp_kses_post( sprintf( __( 'Current timezone: <strong>%s</strong>', 'wpshadow' ), esc_html( wp_timezone_string() ) ) ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Smart Notifications -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Smart Features', 'wpshadow' ),
						'description' => __( 'Reduce noise with intelligent notification filtering.', 'wpshadow' ),
						'icon'        => 'dashicons-lightbulb',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_notification_deduplicate',
									'name'        => 'wpshadow_notification_deduplicate',
									'label'       => __( 'Don\'t Repeat Known Issues', 'wpshadow' ),
									'helper_text' => __( 'Only notify about issues the first time they\'re detected. Reduces alert fatigue.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_notification_deduplicate', true ),
								)
							);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_notification_resolved',
									'name'        => 'wpshadow_notification_resolved',
									'label'       => __( 'Notify on Issue Resolution', 'wpshadow' ),
									'helper_text' => __( 'Send a message when previously detected issues are fixed.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_notification_resolved', true ),
								)
							);
							?>
							<?php
						},
					)
				);
				?>

				<!-- Action Buttons -->
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							?>
							<?php submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
								<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
							</a>
							<?php
						},
					)
				);
				?>
			</form>

			<!-- Recent Activity Section -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'settings', 10 );
			}
			?>
		</div>
		<?php
	}
}
