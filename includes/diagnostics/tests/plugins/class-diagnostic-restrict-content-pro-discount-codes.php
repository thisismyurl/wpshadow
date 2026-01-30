<?php
/**
 * Restrict Content Pro Discount Codes Diagnostic
 *
 * RCP discount codes not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.328.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Discount Codes Diagnostic Class
 *
 * @since 1.328.0000
 */
class Diagnostic_RestrictContentProDiscountCodes extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-discount-codes';
	protected static $title = 'Restrict Content Pro Discount Codes';
	protected static $description = 'RCP discount codes not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Active discount codes
		$discount_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_discounts WHERE status = 'active'"
		);

		if ( $discount_count === null ) {
			return null;
		}

		// Check 2: Expired codes not disabled
		$expired_active = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_discounts
			 WHERE status = 'active' AND expiration < NOW() AND expiration != '0000-00-00 00:00:00'"
		);

		if ( $expired_active > 0 ) {
			$issues[] = sprintf( __( '%d expired codes still active (revenue loss)', 'wpshadow' ), $expired_active );
		}

		// Check 3: Unlimited use codes
		$unlimited_codes = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_discounts
			 WHERE status = 'active' AND max_uses = 0"
		);

		if ( $unlimited_codes > 5 ) {
			$issues[] = sprintf( __( '%d unlimited use codes (abuse risk)', 'wpshadow' ), $unlimited_codes );
		}

		// Check 4: 100% discount codes
		$free_codes = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}rcp_discounts
			 WHERE status = 'active' AND (amount = 100 OR unit = 'flat')"
		);

		if ( $free_codes > 0 ) {
			$issues[] = sprintf( __( '%d free/100%% codes (revenue risk)', 'wpshadow' ), $free_codes );
		}

		// Check 5: Code sharing detection
		$sharing_prevention = get_option( 'rcp_prevent_code_sharing', 'no' );
		if ( 'no' === $sharing_prevention ) {
			$issues[] = __( 'No code sharing prevention (abuse)', 'wpshadow' );
		}

		// Check 6: Usage tracking
		$track_usage = get_option( 'rcp_track_discount_usage', 'no' );
		if ( 'no' === $track_usage ) {
			$issues[] = __( 'Usage not tracked (no analytics)', 'wpshadow' );
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
				__( 'RCP discount codes have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-discount-codes',
		);
	}
}
