<?php
/**
 * Booking Reminder System Diagnostic
 *
 * Booking reminders flooding users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.624.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Reminder System Diagnostic Class
 *
 * @since 1.624.0000
 */
class Diagnostic_BookingReminderSystem extends Diagnostic_Base {

	protected static $slug = 'booking-reminder-system';
	protected static $title = 'Booking Reminder System';
	protected static $description = 'Booking reminders flooding users';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: Verify reminders are enabled
		$reminders_enabled = get_option( 'booking_reminder_enabled', 0 );
		if ( ! $reminders_enabled ) {
			$issues[] = 'Booking reminders not enabled';
		}
		
		// Check 2: Check reminder frequency
		$reminder_interval = get_option( 'booking_reminder_interval', 0 );
		if ( $reminder_interval <= 0 ) {
			$issues[] = 'Reminder interval not configured';
		}
		
		// Check 3: Verify reminder limit per booking
		$reminder_limit = get_option( 'booking_reminder_limit', 0 );
		if ( $reminder_limit > 3 ) {
			$issues[] = 'Reminder limit too high (over 3 per booking)';
		}
		
		// Check 4: Check for queue throttling
		$queue_throttle = get_option( 'booking_reminder_queue_throttle', 0 );
		if ( ! $queue_throttle ) {
			$issues[] = 'Reminder queue throttling not enabled';
		}
		
		// Check 5: Verify email template configuration
		$reminder_template = get_option( 'booking_reminder_email_template', '' );
		if ( empty( $reminder_template ) ) {
			$issues[] = 'Reminder email template not configured';
		}
		
		// Check 6: Check for cleanup of sent reminders
		$cleanup = get_option( 'booking_reminder_cleanup', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Sent reminder cleanup not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d booking reminder performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/booking-reminder-system',
			);
		}
		
		return null;
	}
}
