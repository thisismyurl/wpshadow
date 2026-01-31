<?php
/**
 * Event Calendar iCal Security Diagnostic
 *
 * Event iCal feeds publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.592.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Calendar iCal Security Diagnostic Class
 *
 * @since 1.592.0000
 */
class Diagnostic_EventCalendarIcalSecurity extends Diagnostic_Base {

	protected static $slug = 'event-calendar-ical-security';
	protected static $title = 'Event Calendar iCal Security';
	protected static $description = 'Event iCal feeds publicly accessible';
	protected static $family = 'security';

	public static function check() {
		// Check for popular event calendar plugins
		$has_calendar = class_exists( 'Tribe__Events__Main' ) ||
		                class_exists( 'EM_Events' ) ||
		                defined( 'EVENTS_CALENDAR_VERSION' ) ||
		                get_option( 'ical_feed_enabled', '' ) !== '';
		
		if ( ! $has_calendar ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: iCal feed enabled
		$feed_enabled = get_option( 'ical_feed_enabled', 'yes' );
		if ( 'no' === $feed_enabled ) {
			return null; // Not using iCal feeds
		}
		
		// Check 2: Feed authentication
		$require_auth = get_option( 'ical_require_authentication', 'no' );
		if ( 'no' === $require_auth ) {
			$issues[] = __( 'No authentication (public access)', 'wpshadow' );
		}
		
		// Check 3: Private event filtering
		$hide_private = get_option( 'ical_hide_private_events', 'no' );
		if ( 'no' === $hide_private ) {
			$issues[] = __( 'Private events exposed (data leak)', 'wpshadow' );
		}
		
		// Check 4: Rate limiting
		$rate_limit = get_option( 'ical_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (scraping risk)', 'wpshadow' );
		}
		
		// Check 5: Token expiration
		$token_expiry = get_option( 'ical_token_expiry', 'never' );
		if ( 'never' === $token_expiry ) {
			$issues[] = __( 'Tokens never expire (permanent access)', 'wpshadow' );
		}
		
		// Check 6: Email address exposure
		$hide_emails = get_option( 'ical_hide_organizer_emails', 'no' );
		if ( 'no' === $hide_emails ) {
			$issues[] = __( 'Organizer emails in feeds (spam target)', 'wpshadow' );
		}
		
		// Check 7: CAPTCHA protection
		$captcha = get_option( 'ical_captcha_enabled', 'no' );
		if ( 'no' === $captcha ) {
			$issues[] = __( 'No CAPTCHA (automated scraping)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 75;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 68;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of iCal security issues */
				__( 'Event calendar iCal feeds have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/event-calendar-ical-security',
		);
	}
}
