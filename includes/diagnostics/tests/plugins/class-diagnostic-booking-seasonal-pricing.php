<?php
/**
 * Booking Seasonal Pricing Diagnostic
 *
 * Booking seasonal rates exploitable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.633.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Seasonal Pricing Diagnostic Class
 *
 * @since 1.633.0000
 */
class Diagnostic_BookingSeasonalPricing extends Diagnostic_Base {

	protected static $slug = 'booking-seasonal-pricing';
	protected static $title = 'Booking Seasonal Pricing';
	protected static $description = 'Booking seasonal rates exploitable';
	protected static $family = 'security';

	public static function check() {
		// Check for booking plugins with seasonal pricing
		$has_booking = defined( 'BOOKLY_VERSION' ) ||
		               class_exists( 'WooCommerce_Bookings' ) ||
		               class_exists( 'Amelia' ) ||
		               defined( 'WBCOM_BOOKING_VERSION' );
		
		if ( ! $has_booking ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Seasonal pricing enabled
		$seasonal_enabled = get_option( 'booking_seasonal_pricing', 'off' );
		if ( 'off' === $seasonal_enabled ) {
			return null;
		}
		
		// Check 2: Rate validation
		$server_validation = get_option( 'booking_server_side_validation', 'yes' );
		if ( 'no' === $server_validation ) {
			$issues[] = __( 'Server-side validation disabled (price manipulation risk)', 'wpshadow' );
		}
		
		// Check 3: Client-side pricing calculations
		$calc_method = get_option( 'booking_price_calculation', 'client' );
		if ( 'client' === $calc_method ) {
			$issues[] = __( 'Client-side price calculation (tamperable)', 'wpshadow' );
		}
		
		// Check 4: Rate change audit logging
		$audit_log = get_option( 'booking_rate_audit_log', 'off' );
		if ( 'off' === $audit_log ) {
			$issues[] = __( 'No rate change logging (tampering undetectable)', 'wpshadow' );
		}
		
		// Check 5: Coupon stacking with seasonal rates
		$prevent_stacking = get_option( 'booking_prevent_coupon_stacking', 'no' );
		if ( 'no' === $prevent_stacking ) {
			$issues[] = __( 'Coupon stacking allowed (revenue loss)', 'wpshadow' );
		}
		
		// Check 6: Rate caching
		$cache_rates = get_option( 'booking_cache_rates', 'yes' );
		if ( 'yes' === $cache_rates ) {
			$issues[] = __( 'Rate caching enabled (stale prices shown)', 'wpshadow' );
		}
		
		// Check 7: Minimum booking value
		$min_booking = get_option( 'booking_minimum_value', 0 );
		if ( $min_booking === 0 ) {
			$issues[] = __( 'No minimum booking value ($0 exploits possible)', 'wpshadow' );
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
				/* translators: %s: list of seasonal pricing security issues */
				__( 'Booking seasonal pricing has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-seasonal-pricing',
		);
	}
}
