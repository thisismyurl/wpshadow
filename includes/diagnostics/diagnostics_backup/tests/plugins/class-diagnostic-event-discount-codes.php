<?php
/**
 * Event Discount Codes Diagnostic
 *
 * Event discount codes exploitable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.597.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Discount Codes Diagnostic Class
 *
 * @since 1.597.0000
 */
class Diagnostic_EventDiscountCodes extends Diagnostic_Base {

	protected static $slug = 'event-discount-codes';
	protected static $title = 'Event Discount Codes';
	protected static $description = 'Event discount codes exploitable';
	protected static $family = 'security';

	public static function check() {
		// Check for event/booking plugins with discount codes
		$has_discounts = class_exists( 'Tribe__Events__Main' ) || class_exists( 'Event_Espresso_Core' ) || class_exists( 'WC_Bookings' );
		
		if ( ! $has_discounts ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Discount codes exist
		$discount_count = 0;
		
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$discount_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
					'tribe_events_coupon'
				)
			);
		}
		
		if ( $discount_count === 0 ) {
			return null;
		}
		
		// Check 2: Expired codes not disabled
		$expired_codes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				 WHERE pm.meta_key = %s AND pm.meta_value < %s AND p.post_status = 'publish'",
				'_coupon_expiry_date',
				current_time( 'timestamp' )
			)
		);
		
		if ( $expired_codes > 0 ) {
			$issues[] = sprintf( __( '%d expired discount codes still active (revenue loss)', 'wpshadow' ), $expired_codes );
		}
		
		// Check 3: Usage limits not set
		$unlimited_codes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND (meta_value = '0' OR meta_value = '' OR meta_value IS NULL)",
				'_coupon_usage_limit'
			)
		);
		
		if ( $unlimited_codes > 5 ) {
			$issues[] = sprintf( __( '%d discount codes with no usage limit (abuse risk)', 'wpshadow' ), $unlimited_codes );
		}
		
		// Check 4: Code stacking enabled
		$allow_stacking = get_option( 'event_allow_coupon_stacking', false );
		if ( $allow_stacking ) {
			$issues[] = __( 'Discount code stacking enabled (unintended discounts)', 'wpshadow' );
		}
		
		// Check 5: Usage logging
		$log_usage = get_option( 'event_log_coupon_usage', false );
		if ( ! $log_usage ) {
			$issues[] = __( 'Discount code usage not logged (no fraud detection)', 'wpshadow' );
		}
		
		// Check 6: Public code exposure
		$public_codes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				'tribe_events_coupon'
			)
		);
		
		if ( $public_codes > 0 ) {
			$issues[] = sprintf( __( '%d discount codes publicly listed (unauthorized sharing)', 'wpshadow' ), $public_codes );
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
				/* translators: %s: list of discount code security issues */
				__( 'Event discount codes have %d security/revenue issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/event-discount-codes',
		);
	}
}
