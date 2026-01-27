<?php
declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Reporting\Notification_Manager;

/**
 * Notification Preferences Form
 *
 * UI for managing notification settings.
 * Alert type toggles, email management, frequency selection.
 *
 * Features:
 * - Alert type preferences
 * - Multiple email management
 * - Frequency selector
 * - Subscribe/unsubscribe
 * - Test notifications
 */
class Notification_Preferences_Form {

	/**
	 * Render preferences form
	 *
	 * @return string HTML output
	 */
	public static function render(): string {
		ob_start();
		?>
		<div class="wpshadow-notification-preferences">
			<div class="wps-page-header">
				<h1><?php esc_html_e( 'Notification Preferences', 'wpshadow' ); ?></h1>
				<p class="wps-version-tag">v<?php echo esc_html( WPSHADOW_VERSION ); ?></p>
			</div>
			
			<!-- Alert Types Section -->
			<div class="preferences-card alert-types-card">
				<h3><?php esc_html_e( 'Alert Types', 'wpshadow' ); ?></h3>
				<p><?php esc_html_e( 'Choose which events should trigger notifications:', 'wpshadow' ); ?></p>
				
				<div class="alert-types-grid">
					<?php echo wp_kses_post( self::render_alert_toggles() ); ?>
				</div>
			</div>
			
			<!-- Report Subscriptions Section -->
			<div class="preferences-card subscriptions-card">
				<h3><?php esc_html_e( 'Report Subscriptions', 'wpshadow' ); ?></h3>
				<p><?php esc_html_e( 'Subscribe to regular performance reports:', 'wpshadow' ); ?></p>
				
				<div class="subscriptions-list">
					<?php echo wp_kses_post( self::render_subscriptions() ); ?>
				</div>
				
				<div class="add-subscription">
					<input type="email" id="new_subscription_email" placeholder="<?php esc_attr_e( 'Enter email address', 'wpshadow' ); ?>" />
					<select id="new_subscription_frequency">
						<option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
						<option value="weekly" selected><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
						<option value="monthly"><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
					</select>
					<button type="button" class="wps-btn wps-btn--primary" id="add-subscription-btn">
						<?php esc_html_e( 'Subscribe', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
			
			<!-- Email Settings Section -->
			<div class="preferences-card email-settings-card">
				<h3><?php esc_html_e( 'Email Settings', 'wpshadow' ); ?></h3>
				
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="notification_email">
							<?php esc_html_e( 'Default Email', 'wpshadow' ); ?>
						</label>
						<input type="email" id="notification_email" name="notification_email" 
							value="<?php echo esc_attr( get_option( 'admin_email', '' ) ); ?>" />
						<span class="wps-help-text">
							<?php esc_html_e( 'Primary email for notifications', 'wpshadow' ); ?>
						</span>
					</div>

					<div class="wps-form-group">
						<label class="wps-label">
							<?php esc_html_e( 'Digest Mode', 'wpshadow' ); ?>
						</label>
						<label>
							<input type="checkbox" id="digest_mode" name="digest_mode" value="1" />
							<?php esc_html_e( 'Send batched notifications (instead of individual alerts)', 'wpshadow' ); ?>
						</label>
						<span class="wps-help-text">
							<?php esc_html_e( 'Reduces email frequency by grouping related notifications', 'wpshadow' ); ?>
						</span>
					</div>

					<div class="wps-form-group">
						<label class="wps-label">
							<?php esc_html_e( 'Test Notification', 'wpshadow' ); ?>
						</label>
						<button type="button" class="button" id="send-test-btn">
							<?php esc_html_e( 'Send Test Email', 'wpshadow' ); ?>
						</button>
						<span class="wps-help-text">
							<?php esc_html_e( 'Send a test notification to verify email delivery', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			</div>
			
			<!-- Statistics Section -->
			<div class="preferences-card stats-card">
				<h3><?php esc_html_e( 'Notification Statistics', 'wpshadow' ); ?></h3>
				<?php echo wp_kses_post( self::render_statistics() ); ?>
			</div>
			
			<!-- Save Button -->
			<div class="preferences-actions">
				<button type="button" class="wps-btn wps-btn--primary" id="save-preferences">
					<?php esc_html_e( 'Save Preferences', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render alert type toggles
	 *
	 * @return string HTML
	 */
	private static function render_alert_toggles(): string {
		$preferences = Notification_Manager::get_default_preferences();

		$alert_types = array(
			'critical_issue'   => array(
				'label'       => __( 'Critical Issues', 'wpshadow' ),
				'description' => __( 'Severe problems requiring immediate attention', 'wpshadow' ),
			),
			'auto_fix_failed'  => array(
				'label'       => __( 'Auto-Fix Failures', 'wpshadow' ),
				'description' => __( 'Notifications when automated fixes fail', 'wpshadow' ),
			),
			'anomaly_detected' => array(
				'label'       => __( 'Anomalies Detected', 'wpshadow' ),
				'description' => __( 'Unusual system activity or patterns', 'wpshadow' ),
			),
			'daily_report'     => array(
				'label'       => __( 'Daily Report', 'wpshadow' ),
				'description' => __( 'Summary of daily system activity and fixes', 'wpshadow' ),
			),
			'weekly_report'    => array(
				'label'       => __( 'Weekly Report', 'wpshadow' ),
				'description' => __( 'Comprehensive weekly performance report', 'wpshadow' ),
			),
			'monthly_report'   => array(
				'label'       => __( 'Monthly Report', 'wpshadow' ),
				'description' => __( 'Executive monthly summary', 'wpshadow' ),
			),
		);

		$html = '';
		foreach ( $alert_types as $key => $config ) {
			$is_enabled = $preferences[ $key ] ?? false;
			$html      .= sprintf(
				'<div class="alert-type-item">
					<label class="alert-toggle">
						<input type="checkbox" class="alert-checkbox" name="alerts[]" value="%s" %s />
						<span class="toggle-label">%s</span>
					</label>
					<p class="alert-description">%s</p>
				</div>',
				esc_attr( $key ),
				checked( $is_enabled, true, false ),
				esc_html( $config['label'] ),
				esc_html( $config['description'] )
			);
		}

		return $html;
	}

	/**
	 * Render subscriptions list
	 *
	 * @return string HTML
	 */
	private static function render_subscriptions(): string {
		$stats = Notification_Manager::get_statistics();

		$html = '<table class="subscriptions-table">
			<thead>
				<tr>
					<th>' . esc_html__( 'Email', 'wpshadow' ) . '</th>
					<th>' . esc_html__( 'Frequency', 'wpshadow' ) . '</th>
					<th>' . esc_html__( 'Status', 'wpshadow' ) . '</th>
					<th>' . esc_html__( 'Actions', 'wpshadow' ) . '</th>
				</tr>
			</thead>
			<tbody>';

		// Sample subscriptions
		$subscriptions = array(
			array(
				'email'     => get_option( 'admin_email', '' ),
				'frequency' => 'weekly',
				'status'    => 'active',
			),
		);

		foreach ( $subscriptions as $sub ) {
			$html .= sprintf(
				'<tr>
					<td>%s</td>
					<td>%s</td>
					<td><span class="status-badge status-%s">%s</span></td>
					<td>
						<button type="button" class="button button-small unsubscribe-btn" data-email="%s">
							%s
						</button>
					</td>
				</tr>',
				esc_html( $sub['email'] ),
				esc_html( ucfirst( $sub['frequency'] ) ),
				esc_attr( $sub['status'] ),
				esc_html( ucfirst( $sub['status'] ) ),
				esc_attr( $sub['email'] ),
				esc_html__( 'Unsubscribe', 'wpshadow' )
			);
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Render statistics
	 *
	 * @return string HTML
	 */
	private static function render_statistics(): string {
		$stats = Notification_Manager::get_statistics();

		$html = '<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-number">' . esc_html( (string) $stats['total_subscribers'] ) . '</div>
				<div class="stat-label">' . esc_html__( 'Subscribers', 'wpshadow' ) . '</div>
			</div>
			<div class="stat-card">
				<div class="stat-number">' . esc_html( (string) $stats['daily_subscribers'] ) . '</div>
				<div class="stat-label">' . esc_html__( 'Daily', 'wpshadow' ) . '</div>
			</div>
			<div class="stat-card">
				<div class="stat-number">' . esc_html( (string) $stats['weekly_subscribers'] ) . '</div>
				<div class="stat-label">' . esc_html__( 'Weekly', 'wpshadow' ) . '</div>
			</div>
			<div class="stat-card">
				<div class="stat-number">' . esc_html( (string) $stats['monthly_subscribers'] ) . '</div>
				<div class="stat-label">' . esc_html__( 'Monthly', 'wpshadow' ) . '</div>
			</div>
		</div>';

		return $html;
	}
}
