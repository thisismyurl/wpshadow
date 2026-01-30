<?php
/**
 * BookingPress Email Notifications Diagnostic
 *
 * BookingPress email notifications misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.460.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Email Notifications Diagnostic Class
 *
 * @since 1.460.0000
 */
class Diagnostic_BookingpressEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'bookingpress-email-notifications';
	protected static $title = 'BookingPress Email Notifications';
	protected static $description = 'BookingPress email notifications misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Customer notifications
		$customer_notify = get_option( 'bookingpress_customer_notifications', 'yes' );
		if ( 'no' === $customer_notify ) {
			$issues[] = __( 'Customer notifications disabled (poor experience)', 'wpshadow' );
		}
		
		// Check 2: Admin notifications
		$admin_notify = get_option( 'bookingpress_admin_notifications', 'yes' );
		if ( 'no' === $admin_notify ) {
			$issues[] = __( 'Admin notifications disabled (missed bookings)', 'wpshadow' );
		}
		
		// Check 3: Email templates
		$templates = get_option( 'bookingpress_email_templates', array() );
		if ( empty( $templates ) ) {
			$issues[] = __( 'No custom email templates (generic emails)', 'wpshadow' );
		}
		
		// Check 4: Email queue
		$queue_enabled = get_option( 'bookingpress_email_queue', 'no' );
		if ( 'no' === $queue_enabled ) {
			$issues[] = __( 'Email queue disabled (blocking requests)', 'wpshadow' );
		}
		
		// Check 5: Reminder emails
		$reminders = get_option( 'bookingpress_reminder_emails', 'no' );
		if ( 'no' === $reminders ) {
			$issues[] = __( 'Reminder emails disabled (no-shows likely)', 'wpshadow' );
		}
		
		// Check 6: Email logging
		$logging = get_option( 'bookingpress_email_logging', 'no' );
		if ( 'no' === $logging ) {
			$issues[] = __( 'Email not logged (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 57;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 51;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'BookingPress email has %d notification issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bookingpress-email-notifications',
		);
	}
}
