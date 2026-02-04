<?php
declare(strict_types=1);

namespace WPShadow\Reporting;

use WPShadow\Core\KPI_Tracker;

/**
 * Notification Manager for Reports
 *
 * Manages scheduled report delivery and notifications.
 * Sends email reports, Slack notifications, webhooks.
 *
 * Features:
 * - Scheduled report delivery
 * - Email notifications
 * - Slack integration (Pro)
 * - Webhook notifications
 * - Alert aggregation
 * - Unsubscribe management
 *
 * Philosophy: Keep users informed without spam.
 */
class Notification_Manager {

	const SCHEDULE_DAILY   = 'wpshadow_daily_report';
	const SCHEDULE_WEEKLY  = 'wpshadow_weekly_report';
	const SCHEDULE_MONTHLY = 'wpshadow_monthly_report';

	/**
	 * Schedule report delivery
	 *
	 * @param string $frequency Frequency (daily, weekly, monthly)
	 * @param string $email Email to send to
	 * @param int    $time Hour to send (0-23)
	 *
	 * @return bool Scheduled successfully
	 */
	public static function schedule_report( string $frequency, string $email, int $time = 8 ): bool {
		$frequency = sanitize_key( $frequency );
		$email     = sanitize_email( $email );
		$time      = max( 0, min( 23, intval( $time ) ) );

		if ( ! in_array( $frequency, array( 'daily', 'weekly', 'monthly' ), true ) ) {
			return false;
		}

		$schedule = get_option( 'wpshadow_report_schedules', array() );

		$schedule[] = array(
			'frequency'  => $frequency,
			'email'      => $email,
			'time'       => $time,
			'enabled'    => true,
			'created_at' => current_time( 'mysql' ),
		);

		update_option( 'wpshadow_report_schedules', $schedule );

		// Schedule cron if not exists
		self::ensure_cron_scheduled( $frequency );

		KPI_Tracker::record_action( 'report_scheduled', 1 );

		return true;
	}

	/**
	 * Unsubscribe from report
	 *
	 * @param string $email Email to unsubscribe
	 * @param string $frequency Frequency to stop
	 */
	public static function unsubscribe_report( string $email, string $frequency = '' ): void {
		$email    = sanitize_email( $email );
		$schedule = get_option( 'wpshadow_report_schedules', array() );

		$schedule = array_filter(
			$schedule,
			function ( $s ) use ( $email, $frequency ) {
				if ( $s['email'] !== $email ) {
					return true;
				}

				if ( empty( $frequency ) ) {
					return false; // Remove all
				}

				return $s['frequency'] !== $frequency;
			}
		);

		update_option( 'wpshadow_report_schedules', array_values( $schedule ) );
	}

	/**
	 * Send report immediately
	 *
	 * @param string $email Email to send to
	 * @param string $frequency Report frequency (determines date range)
	 *
	 * @return bool Sent successfully
	 */
	public static function send_report_now( string $email, string $frequency = 'daily' ): bool {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return false;
		}

		// Determine date range
		$end_date   = date( 'Y-m-d' );
		$start_date = match ( $frequency ) {
			'daily' => date( 'Y-m-d' ),
			'weekly' => date( 'Y-m-d', strtotime( '-7 days' ) ),
			'monthly' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			default => date( 'Y-m-d' ),
		};

		// Generate report
		$report = Report_Generator::generate_report( $start_date, $end_date, 'summary' );

		// Format for email
		$html = Report_Generator::export_html( $report );

		// Send email
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$subject = 'WPShadow Report: ' . ucfirst( $frequency );

		$sent = wp_mail( $email, $subject, $html, $headers );

		if ( $sent ) {
			KPI_Tracker::record_action( 'report_sent', 1 );
		}

		return $sent;
	}

	/**
	 * Send alert notification
	 *
	 * @param string $alert_type Type of alert
	 * @param array  $data Alert data
	 *
	 * @return bool Sent successfully
	 */
	public static function send_alert( string $alert_type, array $data ): bool {
		$alert_type = sanitize_key( $alert_type );

		// Get subscribers for this alert type
		$preferences = get_option( 'wpshadow_notification_preferences', array() );

		$recipients = array();
		foreach ( $preferences as $email => $prefs ) {
			if ( ! isset( $prefs[ $alert_type ] ) || $prefs[ $alert_type ] !== true ) {
				continue;
			}

			$recipients[] = sanitize_email( $email );
		}

		if ( empty( $recipients ) ) {
			return false;
		}

		// Build alert message
		$message = self::format_alert_message( $alert_type, $data );

		$sent = false;
		foreach ( $recipients as $email ) {
			$result = wp_mail(
				$email,
				'WPShadow Alert: ' . ucfirst( $alert_type ),
				$message,
				array( 'Content-Type: text/html; charset=UTF-8' )
			);

			if ( $result ) {
				$sent = true;
			}
		}

		if ( $sent ) {
			KPI_Tracker::record_action( 'alert_sent', 1 );
		}

		return $sent;
	}

	/**
	 * Format alert message
	 *
	 * @param string $alert_type Alert type
	 * @param array  $data Alert data
	 *
	 * @return string Formatted message
	 */
	private static function format_alert_message( string $alert_type, array $data ): string {
		$message = '<h2>' . ucfirst( $alert_type ) . '</h2>';

		switch ( $alert_type ) {
			case 'critical_issue':
				$message .= sprintf(
					'<p>A critical issue was detected: %s</p>',
					esc_html( $data['description'] ?? 'Unknown' )
				);
				$message .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=wpshadow' ) ) . '">View Details</a></p>';
				break;

			case 'auto_fix_failed':
				$message .= sprintf(
					'<p>An auto-fix failed: %s</p>',
					esc_html( $data['treatment'] ?? 'Unknown' )
				);
				$message .= sprintf(
					'<p>Error: %s</p>',
					esc_html( $data['error'] ?? 'Unknown error' )
				);
				break;

			case 'anomaly_detected':
				$message .= '<p>Unusual system activity detected:</p><ul>';
				foreach ( $data['anomalies'] ?? array() as $anomaly ) {
					$message .= '<li>' . esc_html( $anomaly ) . '</li>';
				}
				$message .= '</ul>';
				break;

			default:
				$message .= '<p>An event occurred in WPShadow.</p>';
		}

		return $message;
	}

	/**
	 * Set notification preferences for email
	 *
	 * @param string $email Email address
	 * @param array  $preferences Alert type => enabled
	 */
	public static function set_preferences( string $email, array $preferences ): void {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return;
		}

		$all_prefs           = get_option( 'wpshadow_notification_preferences', array() );
		$all_prefs[ $email ] = $preferences;

		update_option( 'wpshadow_notification_preferences', $all_prefs );
	}

	/**
	 * Get notification preferences
	 *
	 * @param string $email Email address
	 *
	 * @return array Preferences
	 */
	public static function get_preferences( string $email ): array {
		$email = sanitize_email( $email );

		$all_prefs = get_option( 'wpshadow_notification_preferences', array() );

		return $all_prefs[ $email ] ?? self::get_default_preferences();
	}

	/**
	 * Get default notification preferences
	 *
	 * @return array Default preferences
	 */
	public static function get_default_preferences(): array {
		return array(
			'critical_issue'   => true,
			'auto_fix_failed'  => true,
			'anomaly_detected' => true,
			'daily_report'     => false,
			'weekly_report'    => true,
			'monthly_report'   => false,
		);
	}

	/**
	 * Ensure cron is scheduled
	 *
	 * @param string $frequency Report frequency
	 */
	private static function ensure_cron_scheduled( string $frequency ): void {
		$hook = match ( $frequency ) {
			'daily' => self::SCHEDULE_DAILY,
			'weekly' => self::SCHEDULE_WEEKLY,
			'monthly' => self::SCHEDULE_MONTHLY,
			default => '',
		};

		if ( empty( $hook ) ) {
			return;
		}

		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time(), $frequency, $hook );
		}
	}

	/**
	 * Get notification statistics
	 *
	 * @return array Statistics
	 */
	public static function get_statistics(): array {
		$schedules   = get_option( 'wpshadow_report_schedules', array() );
		$preferences = get_option( 'wpshadow_notification_preferences', array() );

		return array(
			'total_subscribers'   => count( $schedules ),
			'daily_subscribers'   => count( array_filter( $schedules, fn( $s ) => $s['frequency'] === 'daily' ) ),
			'weekly_subscribers'  => count( array_filter( $schedules, fn( $s ) => $s['frequency'] === 'weekly' ) ),
			'monthly_subscribers' => count( array_filter( $schedules, fn( $s ) => $s['frequency'] === 'monthly' ) ),
			'preference_settings' => count( $preferences ),
		);
	}
}
