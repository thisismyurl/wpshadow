<?php
/**
 * Modern Events Calendar Security Diagnostic
 *
 * Modern Events Calendar bookings insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.584.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modern Events Calendar Security Diagnostic Class
 *
 * @since 1.584.0000
 */
class Diagnostic_ModernEventsCalendarSecurity extends Diagnostic_Base {

	protected static $slug = 'modern-events-calendar-security';
	protected static $title = 'Modern Events Calendar Security';
	protected static $description = 'Modern Events Calendar bookings insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'MEC_Main' ) && ! defined( 'MEC_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify captcha on booking forms
		$captcha = get_option( 'mec_booking_captcha', 0 );
		if ( ! $captcha ) {
			$issues[] = 'Booking CAPTCHA not enabled';
		}
		
		// Check 2: Check for SSL on checkout
		if ( ! is_ssl() ) {
			$issues[] = 'SSL not enabled for event booking';
		}
		
		// Check 3: Verify attendee data visibility
		$attendee_visibility = get_option( 'mec_attendee_visibility', 'public' );
		if ( 'public' === $attendee_visibility ) {
			$issues[] = 'Attendee data is publicly visible';
		}
		
		// Check 4: Check for booking form validation
		$form_validation = get_option( 'mec_booking_validation', 0 );
		if ( ! $form_validation ) {
			$issues[] = 'Booking form validation not enabled';
		}
		
		// Check 5: Verify email verification for attendees
		$email_verification = get_option( 'mec_email_verification', 0 );
		if ( ! $email_verification ) {
			$issues[] = 'Email verification not enabled for bookings';
		}
		
		// Check 6: Check for admin-only attendee export
		$export_restriction = get_option( 'mec_attendee_export_restriction', 0 );
		if ( ! $export_restriction ) {
			$issues[] = 'Attendee export not restricted to admins';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Modern Events Calendar security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/modern-events-calendar-security',
			);
		}
		
		return null;
	}
}
