<?php
/**
 * Booking Sync External Calendars Diagnostic
 *
 * Booking calendar sync insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.636.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Sync External Calendars Diagnostic Class
 *
 * @since 1.636.0000
 */
class Diagnostic_BookingSyncExternalCalendars extends Diagnostic_Base {

	protected static $slug = 'booking-sync-external-calendars';
	protected static $title = 'Booking Sync External Calendars';
	protected static $description = 'Booking calendar sync insecure';
	protected static $family = 'security';

	public static function check() {
		// Check for booking plugins with calendar sync
		$has_booking = class_exists( 'WooCommerce_Bookings' ) ||
		               defined( 'WPDEV_BK_VERSION' ) ||
		               class_exists( 'STM_Booking' );
		
		if ( ! $has_booking ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: External calendar sync enabled
		$sync_enabled = get_option( 'booking_calendar_sync_enabled', false );
		if ( ! $sync_enabled ) {
			return null;
		}
		
		// Check 2: iCal feed URL exposed
		$ical_public = get_option( 'booking_ical_public_feeds', false );
		if ( $ical_public ) {
			$issues[] = __( 'Public iCal feeds enabled (booking data exposure)', 'wpshadow' );
		}
		
		// Check 3: Authentication for external calendars
		$auth_method = get_option( 'booking_sync_auth_method', 'none' );
		if ( 'none' === $auth_method ) {
			$issues[] = __( 'No authentication for calendar sync (security risk)', 'wpshadow' );
		}
		
		// Check 4: Sync frequency
		$sync_frequency = get_option( 'booking_sync_frequency', 60 ); // minutes
		if ( $sync_frequency < 30 ) {
			$issues[] = sprintf( __( 'Calendar sync every %d minutes (API rate limits)', 'wpshadow' ), $sync_frequency );
		}
		
		// Check 5: SSL verification
		$verify_ssl = get_option( 'booking_sync_verify_ssl', true );
		if ( ! $verify_ssl ) {
			$issues[] = __( 'SSL verification disabled for sync (MITM risk)', 'wpshadow' );
		}
		
		// Check 6: Error logging
		$log_errors = get_option( 'booking_sync_log_errors', false );
		if ( ! $log_errors ) {
			$issues[] = __( 'Sync error logging disabled (troubleshooting difficult)', 'wpshadow' );
		}
		
		// Check 7: Webhook authentication
		$webhook_secret = get_option( 'booking_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = __( 'No webhook secret configured (unauthorized updates)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 84;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 77;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of calendar sync security issues */
				__( 'Booking calendar sync has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-sync-external-calendars',
		);
	}
}
