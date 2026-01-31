<?php
/**
 * Events Manager Attendee Privacy Diagnostic
 *
 * Events Manager attendee list public.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.578.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Manager Attendee Privacy Diagnostic Class
 *
 * @since 1.578.0000
 */
class Diagnostic_EventsManagerAttendeePrivacy extends Diagnostic_Base {

	protected static $slug = 'events-manager-attendee-privacy';
	protected static $title = 'Events Manager Attendee Privacy';
	protected static $description = 'Events Manager attendee list public';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EM_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Attendee list publicly visible
		$public_attendees = get_option( 'dbem_attendees_public', true );
		if ( $public_attendees ) {
			$issues[] = __( 'Attendee lists publicly visible (privacy concern)', 'wpshadow' );
		}
		
		// Check 2: Show attendee names
		$show_names = get_option( 'dbem_bookings_show_names', true );
		if ( $show_names && $public_attendees ) {
			$issues[] = __( 'Attendee names publicly displayed', 'wpshadow' );
		}
		
		// Check 3: Email addresses visible
		$show_emails = get_option( 'dbem_bookings_show_emails', false );
		if ( $show_emails ) {
			$issues[] = __( 'Attendee email addresses visible (GDPR violation)', 'wpshadow' );
		}
		
		// Check 4: Booking form privacy policy
		$privacy_policy = get_option( 'dbem_bookings_privacy_policy', '' );
		if ( empty( $privacy_policy ) ) {
			$issues[] = __( 'No privacy policy link in booking form', 'wpshadow' );
		}
		
		// Check 5: GDPR consent checkbox
		$consent_required = get_option( 'dbem_bookings_consent_required', false );
		if ( ! $consent_required ) {
			$issues[] = __( 'GDPR consent not required for bookings', 'wpshadow' );
		}
		
		// Check 6: Data retention policy
		$retention = get_option( 'dbem_bookings_data_retention', 0 );
		if ( $retention === 0 ) {
			$issues[] = __( 'No data retention policy configured (keeps data indefinitely)', 'wpshadow' );
		}
		
		// Check 7: Count of bookings with personal data
		$booking_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}em_bookings WHERE booking_status = 1"
		);
		
		if ( $booking_count > 100 && $public_attendees ) {
			$issues[] = sprintf( __( '%d bookings with potentially exposed personal data', 'wpshadow' ), $booking_count );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 80;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of privacy issues */
				__( 'Events Manager has %d attendee privacy issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/events-manager-attendee-privacy',
		);
	}
}
