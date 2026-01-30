<?php
/**
 * Booking Confirmation Emails Diagnostic
 *
 * Booking confirmation emails delayed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.621.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Confirmation Emails Diagnostic Class
 *
 * @since 1.621.0000
 */
class Diagnostic_BookingConfirmationEmails extends Diagnostic_Base {

	protected static $slug = 'booking-confirmation-emails';
	protected static $title = 'Booking Confirmation Emails';
	protected static $description = 'Booking confirmation emails delayed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'BookingPress' ) && ! function_exists( 'wpb_booking_press_load_textdomain' ) ) {
			return null;
		}

		$issues = array();

		// Check email notification settings
		$email_enabled = get_option( 'bookingpress_email_notifications_enabled', '1' );
		if ( '0' === $email_enabled ) {
			$issues[] = 'booking email notifications disabled';
		}

		// Check for SMTP configuration
		$smtp_configured = get_option( 'bookingpress_smtp_configured', '0' );
		$wp_mail_smtp = is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' );

		if ( '0' === $smtp_configured && ! $wp_mail_smtp && ! defined( 'SMTP_HOST' ) ) {
			$issues[] = 'SMTP not configured (emails may not deliver reliably)';
		}

		// Check for email template customization
		$template = get_option( 'bookingpress_email_template', '' );
		if ( empty( $template ) ) {
			$issues[] = 'email template not customized (using default)';
		}

		// Check email delay/queue settings
		global $wpdb;
		$pending_emails = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bookingpress_email_queue WHERE status = %s",
				'pending'
			)
		);

		if ( $pending_emails > 50 ) {
			$issues[] = "email queue backlog ({$pending_emails} pending emails)";
		}

		// Check for failed email logs
		$failed_emails = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}bookingpress_email_logs
				 WHERE status = %s AND created_at > %s",
				'failed',
				date( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		if ( $failed_emails > 10 ) {
			$issues[] = "recent email delivery failures ({$failed_emails} in last 7 days)";
		}

		// Check for email rate limiting
		$rate_limit = get_option( 'bookingpress_email_rate_limit', '0' );
		if ( '0' === $rate_limit ) {
			$booking_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}bookingpress_entries WHERE created_at > %s",
					date( 'Y-m-d H:i:s', strtotime( '-1 day' ) )
				)
			);

			if ( $booking_count > 100 ) {
				$issues[] = 'no email rate limiting with high booking volume';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Booking confirmation email issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/booking-confirmation-emails',
			);
		}

		return null;
	}
}
