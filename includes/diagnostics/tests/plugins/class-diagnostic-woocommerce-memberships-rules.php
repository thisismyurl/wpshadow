<?php
/**
 * Woocommerce Memberships Rules Diagnostic
 *
 * Woocommerce Memberships Rules issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.643.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Memberships Rules Diagnostic Class
 *
 * @since 1.643.0000
 */
class Diagnostic_WoocommerceMembershipsRules extends Diagnostic_Base {

	protected static $slug = 'woocommerce-memberships-rules';
	protected static $title = 'Woocommerce Memberships Rules';
	protected static $description = 'Woocommerce Memberships Rules issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$has_memberships = function_exists( 'wc_memberships' ) ||
		                    class_exists( 'WC_Memberships' );

		if ( ! $has_memberships ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Membership plans count
		$plan_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				'wc_membership_plan'
			)
		);

		if ( $plan_count === 0 ) {
			return null;
		}

		// Check 2: Content restriction rules
		$restriction_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wc_memberships_restriction_rules'"
		);

		if ( $restriction_count > 100 ) {
			$issues[] = sprintf( __( '%d restriction rules (slow queries)', 'wpshadow' ), $restriction_count );
		}

		// Check 3: Member count
		$member_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'wc_user_membership'
			)
		);

		if ( $member_count > 5000 ) {
			$issues[] = sprintf( __( '%d active members (performance impact)', 'wpshadow' ), number_format( $member_count ) );
		}

		// Check 4: Caching
		$cache_enabled = get_option( 'wc_memberships_cache_enabled', 'no' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Membership queries not cached (redundant checks)', 'wpshadow' );
		}

		// Check 5: Email notifications
		$email_frequency = get_option( 'wc_memberships_email_frequency', 'instant' );
		if ( 'instant' === $email_frequency && $member_count > 1000 ) {
			$issues[] = __( 'Instant emails for large membership (mail queue)', 'wpshadow' );
		}

		// Check 6: Expiry processing
		$expiry_cron = wp_get_scheduled_event( 'wc_memberships_check_expiry' );
		if ( ! $expiry_cron ) {
			$issues[] = __( 'Expiry cron not scheduled (members not expired)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WooCommerce Memberships has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-memberships-rules',
		);
	}
}
