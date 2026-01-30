<?php
/**
 * Restaurant Reservations Spam Diagnostic
 *
 * Restaurant spam reservations accumulating.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.599.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Spam Diagnostic Class
 *
 * @since 1.599.0000
 */
class Diagnostic_RestaurantReservationsSpam extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-spam';
	protected static $title = 'Restaurant Reservations Spam';
	protected static $description = 'Restaurant spam reservations accumulating';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'rtbInit' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: CAPTCHA enabled
		$captcha = get_option( 'rtb_captcha_enabled', 'no' );
		if ( 'no' === $captcha ) {
			$issues[] = __( 'No CAPTCHA (bot spam)', 'wpshadow' );
		}

		// Check 2: Honeypot field
		$honeypot = get_option( 'rtb_honeypot_enabled', 'no' );
		if ( 'no' === $honeypot ) {
			$issues[] = __( 'No honeypot field (simple bot spam)', 'wpshadow' );
		}

		// Check 3: Rate limiting
		$rate_limit = get_option( 'rtb_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (spam floods)', 'wpshadow' );
		}

		// Check 4: Spam reservation count
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type = %s AND post_status = %s",
				'rtb_booking',
				'spam'
			)
		);

		if ( $spam_count > 50 ) {
			$issues[] = sprintf( __( '%d spam reservations (database bloat)', 'wpshadow' ), $spam_count );
		}

		// Check 5: Auto-delete spam
		$auto_delete = get_option( 'rtb_auto_delete_spam', 'no' );
		if ( 'no' === $auto_delete ) {
			$issues[] = __( 'Spam not auto-deleted (manual cleanup)', 'wpshadow' );
		}

		// Check 6: Moderation required
		$require_approval = get_option( 'rtb_require_approval', 'no' );
		if ( 'no' === $require_approval ) {
			$issues[] = __( 'No moderation (instant booking)', 'wpshadow' );
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
				/* translators: %s: list of restaurant reservations spam issues */
				__( 'Restaurant reservations have %d spam issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-spam',
		);
	}
}
