<?php
/**
 * Event Registration Spam Protection Diagnostic
 *
 * Event registrations spam unfiltered.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.591.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Registration Spam Protection Diagnostic Class
 *
 * @since 1.591.0000
 */
class Diagnostic_EventRegistrationSpamProtection extends Diagnostic_Base {

	protected static $slug = 'event-registration-spam-protection';
	protected static $title = 'Event Registration Spam Protection';
	protected static $description = 'Event registrations spam unfiltered';
	protected static $family = 'security';

	public static function check() {
		// Check for event registration plugins
		$has_events = class_exists( 'Event_Espresso' ) ||
		              function_exists( 'tribe_events_register_event_type' ) ||
		              class_exists( 'EM_Events' );
		
		if ( ! $has_events ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CAPTCHA enabled
		$captcha = get_option( 'event_registration_captcha', 'no' );
		if ( 'no' === $captcha ) {
			$issues[] = __( 'No CAPTCHA (bot registrations)', 'wpshadow' );
		}
		
		// Check 2: Rate limiting
		$rate_limit = get_option( 'event_registration_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (spam floods)', 'wpshadow' );
		}
		
		// Check 3: Email verification
		$verify_email = get_option( 'event_registration_verify_email', 'no' );
		if ( 'no' === $verify_email ) {
			$issues[] = __( 'Email not verified (fake registrations)', 'wpshadow' );
		}
		
		// Check 4: Honeypot fields
		$honeypot = get_option( 'event_registration_honeypot', 'no' );
		if ( 'no' === $honeypot ) {
			$issues[] = __( 'No honeypot fields (bot detection)', 'wpshadow' );
		}
		
		// Check 5: Registration approval
		$approval = get_option( 'event_registration_approval', 'auto' );
		if ( 'auto' === $approval ) {
			$issues[] = __( 'Auto-approval (spam accepted)', 'wpshadow' );
		}
		
		// Check 6: Duplicate detection
		$duplicate_check = get_option( 'event_registration_duplicate_check', 'no' );
		if ( 'no' === $duplicate_check ) {
			$issues[] = __( 'No duplicate detection (multiple fake entries)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 72;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 66;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Event registration has %d spam protection issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/event-registration-spam-protection',
		);
	}
}
