<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

/**
 * Notification Manager
 *
 * Manages email and webhook notification preferences and delivery.
 * Implements consent-first approach: users explicitly opt-in to each notification type.
 *
 * Philosophy: Notifications are helpful, not spammy. Users have granular control.
 * Free tier gets critical alerts and weekly digest. Pro tier unlocks daily digest
 * and anomaly alerts (Commandment #2: Free as Possible).
 *
 * Data Storage:
 * - wpshadow_notification_preferences: Array of notification toggles (wp_options)
 *
 * @since 0.6093.1200
 */
class Notification_Manager {

	/**
	 * Get current notification preferences for site
	 *
	 * @return array Associative array of notification settings
	 */
	public static function get_preferences(): array {
		return get_option( 'wpshadow_notification_preferences', self::get_default_preferences() );
	}

	/**
	 * Get default notification preferences (free tier)
	 *
	 * Defaults are conservative: critical only, plus weekly summary.
	 * Users can opt-in to more.
	 *
	 * @return array Default preferences
	 */
	private static function get_default_preferences(): array {
		return array(
			'email_on_critical' => true,   // FREE: Always on - top-severity findings
			'email_on_findings' => false,  // PRO: All new findings
			'daily_digest'      => false,  // PRO: Daily summary email
			'weekly_summary'    => true,   // FREE: Weekly site health digest
			'scan_completion'   => true,   // FREE: Notify when cloud scan completes
			'anomaly_alerts'    => false,  // PRO: Unusual activity detected
			'webhook_enabled'   => false,  // PRO: Send to custom webhook
			'webhook_url'       => '',     // PRO: Webhook URL
		);
	}

	/**
	 * Update notification preferences
	 *
	 * Validates tier constraints: removes pro-only preferences if user is free tier.
	 *
	 * @param array $preferences Preferences to update (partial or full)
	 *
	 * @return bool Success
	 */
	public static function set_preferences( array $preferences ): bool {
		// Get current tier
		$status = Registration_Manager::get_registration_status();
		$tier   = $status['tier'] ?? 'free';

		// Get existing preferences
		$current = self::get_preferences();

		// Merge new preferences
		$updated = array_merge( $current, $preferences );

		// Enforce tier constraints
		if ( $tier === 'free' ) {
			// Free tier: only top-severity, weekly summary, scan completion
			$updated['email_on_findings'] = false;
			$updated['daily_digest']      = false;
			$updated['anomaly_alerts']    = false;
			$updated['webhook_enabled']   = false;
		}

		// Validate webhook URL if enabled
		if ( $updated['webhook_enabled'] && ! empty( $updated['webhook_url'] ) ) {
			if ( ! wp_http_validate_url( $updated['webhook_url'] ) ) {
				return false;
			}
		}

		// Save preferences
		return update_option( 'wpshadow_notification_preferences', $updated );
	}

	/**
	 * Send notification
	 *
	 * Routes notification to appropriate delivery methods (email, webhook, etc).
	 * Respects user preferences and rate limiting.
	 *
	 * @param string $type Notification type (critical, findings, scan_complete, etc)
	 * @param array  $data Notification data (findings, scan results, etc)
	 * @param string $context Optional: context for deduplication (e.g., finding ID)
	 *
	 * @return bool Success
	 */
	public static function send_notification(
		string $type,
		array $data,
		string $context = ''
	): bool {
		// Check if user is registered
		if ( ! Registration_Manager::is_registered() ) {
			return false;
		}

		// Get preferences
		$prefs = self::get_preferences();

		// Map notification type to preference key
		$pref_key = self::map_type_to_preference( $type );

		// Check if user enabled this notification type
		if ( ! isset( $prefs[ $pref_key ] ) || ! $prefs[ $pref_key ] ) {
			return false;
		}

		// Check rate limiting (prevent duplicate notifications)
		if ( ! self::should_send( $type, $context ) ) {
			return false;
		}

		// Send via email (primary method)
		$email_sent = self::send_email_notification( $type, $data );

		// Send to webhook if enabled (secondary method)
		if ( $prefs['webhook_enabled'] && ! empty( $prefs['webhook_url'] ) ) {
			self::send_webhook_notification( $type, $data, $prefs['webhook_url'] );
		}

		// Log notification sent
		if ( $email_sent ) {
			self::log_notification_sent( $type, $context );
		}

		return $email_sent;
	}

	/**
	 * Map notification type to preference key
	 *
	 * @param string $type Notification type
	 *
	 * @return string Preference key or empty if not found
	 */
	private static function map_type_to_preference( string $type ): string {
		$mapping = array(
			'critical'       => 'email_on_critical',
			'findings'       => 'email_on_findings',
			'scan_complete'  => 'scan_completion',
			'daily_digest'   => 'daily_digest',
			'weekly_summary' => 'weekly_summary',
			'anomaly'        => 'anomaly_alerts',
		);

		return $mapping[ $type ] ?? '';
	}

	/**
	 * Check if notification should be sent (rate limiting)
	 *
	 * Prevents duplicate notifications for same context within time window.
	 * E.g., don't send 10 emails for same finding detected multiple times.
	 *
	 * @param string $type Notification type
	 * @param string $context Unique identifier for this notification
	 *
	 * @return bool True if should send, false if rate limited
	 */
	private static function should_send( string $type, string $context = '' ): bool {
		if ( empty( $context ) ) {
			return true; // No rate limiting if no context
		}

		$cache_key = "wpshadow_notif_{$type}_{$context}";

		// Check if notification already sent recently (within 1 hour)
		if ( \WPShadow\Core\Cache_Manager::get( $cache_key, 'wpshadow_cloud' ) ) {
			return false;
		}

		// Mark as sent
		\WPShadow\Core\Cache_Manager::set( $cache_key, true, HOUR_IN_SECONDS , 'wpshadow_cloud');

		return true;
	}

	/**
	 * Send email notification
	 *
	 * Routes through cloud service for better deliverability.
	 * Falls back to wp_mail if cloud service unavailable.
	 *
	 * @param string $type Notification type
	 * @param array  $data Notification data
	 *
	 * @return bool Success
	 */
	private static function send_email_notification( string $type, array $data ): bool {
		// Try cloud API first (better deliverability)
		$response = Cloud_Client::request(
			'POST',
			'/notifications/email',
			array(
				'type' => $type,
				'data' => $data,
			)
		);

		if ( ! isset( $response['error'] ) ) {
			return true;
		}

		// Fallback: send via wp_mail
		$admin_email = get_option( 'admin_email' );
		$subject     = self::get_email_subject( $type );
		$message     = self::get_email_message( $type, $data );

		return wp_mail(
			$admin_email,
			$subject,
			$message,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}

	/**
	 * Send webhook notification
	 *
	 * Sends notification to user-configured webhook endpoint.
	 * Non-blocking: failure doesn't affect main flow.
	 *
	 * @param string $type Notification type
	 * @param array  $data Notification data
	 * @param string $webhook_url Target webhook URL
	 *
	 * @return bool Success
	 */
	private static function send_webhook_notification(
		string $type,
		array $data,
		string $webhook_url
	): bool {
		$payload = array(
			'type'      => $type,
			'timestamp' => current_time( 'mysql' ),
			'site_url'  => esc_url_raw( get_site_url() ),
			'data'      => $data,
		);

		$response = wp_remote_post(
			esc_url_raw( $webhook_url ),
			array(
				'timeout' => 5,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $payload ),
			)
		);

		return ! is_wp_error( $response );
	}

	/**
	 * Get email subject for notification type
	 *
	 * @param string $type Notification type
	 *
	 * @return string Email subject
	 */
	private static function get_email_subject( string $type ): string {
		$subjects = array(
			'critical'       => __( '🚨 WPShadow: Critical Security Alert', 'wpshadow' ),
			'findings'       => __( '📋 WPShadow: New Issues Detected', 'wpshadow' ),
			'scan_complete'  => __( '✅ WPShadow: Cloud Scan Completed', 'wpshadow' ),
			'daily_digest'   => sprintf(
				__( '📊 WPShadow Daily Report - %s', 'wpshadow' ),
				wp_date( 'M d, Y' )
			),
			'weekly_summary' => sprintf(
				__( '📈 WPShadow Weekly Summary - %s', 'wpshadow' ),
				wp_date( 'M d' )
			),
			'anomaly'        => __( '⚠️  WPShadow: Unusual Activity Detected', 'wpshadow' ),
		);

		return $subjects[ $type ] ?? __( 'WPShadow Notification', 'wpshadow' );
	}

	/**
	 * Get email message for notification type
	 *
	 * Returns HTML email message appropriate for notification type.
	 *
	 * @param string $type Notification type
	 * @param array  $data Notification data
	 *
	 * @return string HTML email message
	 */
	private static function get_email_message( string $type, array $data ): string {
		// Start with common header
		$message = sprintf(
			'<html><body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto; color: #333;">' .
			'<h2>%s</h2>' .
			'<p style="font-size: 14px; color: #666;">%s</p>',
			wp_kses_post( self::get_email_subject( $type ) ),
			esc_html( get_bloginfo( 'name' ) )
		);

		// Add type-specific content
		switch ( $type ) {
			case 'critical':
				$message .= '<p><strong>Critical findings require immediate attention!</strong></p>';
				if ( isset( $data['findings'] ) ) {
					$message .= '<ul>';
					foreach ( $data['findings'] as $finding ) {
						$message .= sprintf(
							'<li><strong>%s:</strong> %s</li>',
							esc_html( $finding['id'] ),
							esc_html( $finding['message'] ?? '' )
						);
					}
					$message .= '</ul>';
				}
				break;

			case 'scan_complete':
				$message .= sprintf(
					'<p>Cloud deep scan completed with <strong>%d findings</strong>.</p>',
					intval( $data['findings_count'] ?? 0 )
				);
				break;

			case 'weekly_summary':
				$message .= sprintf(
					'<p>Your site health score: <strong style="color: %s; font-size: 18px;">%d/100</strong></p>',
					$data['health_score'] >= 80 ? '#27ae60' : '#e74c3c',
					intval( $data['health_score'] ?? 0 )
				);
				break;
		}

		// Add call-to-action button
		$dashboard_url = Registration_Manager::get_dashboard_url();
		if ( $dashboard_url ) {
			$message .= sprintf(
				'<p style="margin-top: 20px;"><a href="%s" class="wps-inline-block-p-12-rounded-4">View in Dashboard</a></p>',
				esc_url( $dashboard_url )
			);
		}

		// Footer
		$message .= '</body></html>';

		return $message;
	}

	/**
	 * Log notification sent (for analytics)
	 *
	 * @param string $type Notification type
	 * @param string $context Optional: unique identifier
	 */
	private static function log_notification_sent( string $type, string $context = '' ): void {
		$log = get_option( 'wpshadow_notification_log', array() );

		$log[] = array(
			'timestamp' => current_time( 'mysql' ),
			'type'      => $type,
			'context'   => $context,
		);

		// Keep only last 500 notifications
		$log = array_slice( $log, -500 );

		update_option( 'wpshadow_notification_log', $log );
	}

	/**
	 * Get notification statistics
	 *
	 * @return array Statistics about sent notifications
	 */
	public static function get_statistics(): array {
		$log = get_option( 'wpshadow_notification_log', array() );

		$stats = array(
			'total_sent' => count( $log ),
			'by_type'    => array(),
		);

		foreach ( $log as $entry ) {
			$type                      = $entry['type'] ?? 'unknown';
			$stats['by_type'][ $type ] = ( $stats['by_type'][ $type ] ?? 0 ) + 1;
		}

		return $stats;
	}
}
