<?php
/**
 * Booking API Integration Diagnostic
 *
 * Booking API keys exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.635.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking API Integration Diagnostic Class
 *
 * @since 1.635.0000
 */
class Diagnostic_BookingApiIntegration extends Diagnostic_Base {

	protected static $slug = 'booking-api-integration';
	protected static $title = 'Booking API Integration';
	protected static $description = 'Booking API keys exposed';
	protected static $family = 'security';

	public static function check() {
		// Check for booking plugins with API
		$has_booking_api = defined( 'BOOKLY_VERSION' ) ||
		                   class_exists( 'WooCommerce_Bookings' ) ||
		                   class_exists( 'Amelia' ) ||
		                   defined( 'WBCOM_BOOKING_VERSION' );
		
		if ( ! $has_booking_api ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key in database
		$api_key = get_option( 'booking_api_key', '' );
		if ( ! empty( $api_key ) && ! defined( 'BOOKING_API_KEY' ) ) {
			$issues[] = __( 'API key in database (should be in wp-config.php)', 'wpshadow' );
		}
		
		// Check 2: API authentication
		$auth_method = get_option( 'booking_api_auth', 'none' );
		if ( 'none' === $auth_method ) {
			$issues[] = __( 'No API authentication (security exposure)', 'wpshadow' );
		}
		
		// Check 3: Webhook secret
		$webhook_secret = get_option( 'booking_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = __( 'No webhook secret (unverified callbacks)', 'wpshadow' );
		}
		
		// Check 4: Rate limiting
		$rate_limit = get_option( 'booking_api_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No API rate limiting (abuse risk)', 'wpshadow' );
		}
		
		// Check 5: API logging
		$log_api = get_option( 'booking_api_logging', 'no' );
		if ( 'no' === $log_api ) {
			$issues[] = __( 'API logging disabled (no audit trail)', 'wpshadow' );
		}
		
		// Check 6: SSL enforcement
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using SSL (API keys transmitted unencrypted)', 'wpshadow' );
		}
		
		// Check 7: IP whitelist
		$ip_whitelist = get_option( 'booking_api_ip_whitelist', array() );
		if ( empty( $ip_whitelist ) ) {
			$issues[] = __( 'No IP whitelist (worldwide API access)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 88;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 82;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of API security issues */
				__( 'Booking API integration has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-api-integration',
		);
	}
}
