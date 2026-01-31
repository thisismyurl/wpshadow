<?php
/**
 * Appointment Booking Notifications Diagnostic
 *
 * Appointment notifications excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.606.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appointment Booking Notifications Diagnostic Class
 *
 * @since 1.606.0000
 */
class Diagnostic_AppointmentBookingNotifications extends Diagnostic_Base {

	protected static $slug = 'appointment-booking-notifications';
	protected static $title = 'Appointment Booking Notifications';
	protected static $description = 'Appointment notifications excessive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'EDD_Bookings' ) && ! defined( 'WAPPOINTMENT_VERSION' ) && ! class_exists( 'WPBS_Init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Notification frequency.
		$frequency = get_option( 'appointment_notification_frequency', 'immediate' );
		if ( 'excessive' === $frequency ) {
			$issues[] = 'notification frequency excessive (customer spam risk)';
		}

		// Check 2: Email queue processing.
		$queue_enabled = get_option( 'appointment_email_queue_enabled', '1' );
		if ( '0' === $queue_enabled ) {
			$issues[] = 'email queue disabled (slow page loads during notifications)';
		}

		// Check 3: Duplicate prevention.
		$prevent_dups = get_option( 'appointment_prevent_duplicate_notifications', '1' );
		if ( '0' === $prevent_dups ) {
			$issues[] = 'duplicate notification prevention disabled (customers receive multiples)';
		}

		// Check 4: Notification templates.
		$templates = get_option( 'appointment_notification_templates', array() );
		$template_count = is_array( $templates ) ? count( $templates ) : 0;
		if ( $template_count < 2 ) {
			$issues[] = "only {$template_count} notification template(s) configured (limited customization)";
		}

		// Check 5: Reminder timing.
		$reminder_hours = get_option( 'appointment_reminder_hours_before', 24 );
		if ( $reminder_hours < 1 || $reminder_hours > 168 ) {
			$issues[] = "reminder timing {$reminder_hours} hours (unrealistic)";
		}

		// Check 6: Unsubscribe option.
		$allow_unsub = get_option( 'appointment_allow_unsubscribe', '1' );
		if ( '0' === $allow_unsub ) {
			$issues[] = 'unsubscribe option disabled (legal compliance issue)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Appointment booking notification issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/appointment-booking-notifications',
			);
		}

		return null;
	}
}
