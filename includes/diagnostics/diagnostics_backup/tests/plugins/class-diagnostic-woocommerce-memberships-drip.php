<?php
/**
 * Woocommerce Memberships Drip Diagnostic
 *
 * Woocommerce Memberships Drip issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.642.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Memberships Drip Diagnostic Class
 *
 * @since 1.642.0000
 */
class Diagnostic_WoocommerceMembershipsDrip extends Diagnostic_Base {

	protected static $slug = 'woocommerce-memberships-drip';
	protected static $title = 'Woocommerce Memberships Drip';
	protected static $description = 'Woocommerce Memberships Drip issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WC_Memberships' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Drip content rules exist
		$drip_rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_wc_memberships_content_restriction_rules'
			)
		);
		
		if ( $drip_rules === 0 ) {
			return null;
		}
		
		// Check 2: Drip schedule validation
		$invalid_schedules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND (meta_value = '' OR meta_value IS NULL OR meta_value = '0')",
				'_wc_memberships_drip_schedule'
			)
		);
		
		if ( $invalid_schedules > 0 ) {
			$issues[] = sprintf( __( '%d drip rules with invalid schedules', 'wpshadow' ), $invalid_schedules );
		}
		
		// Check 3: Cron job for drip content
		$cron_scheduled = wp_next_scheduled( 'wc_memberships_grant_access_to_dripped_content' );
		if ( ! $cron_scheduled ) {
			$issues[] = __( 'Drip content cron job not scheduled', 'wpshadow' );
		}
		
		// Check 4: Member access logs
		$log_access = get_option( 'wc_memberships_log_access', false );
		if ( ! $log_access ) {
			$issues[] = __( 'Member access logging not enabled (compliance risk)', 'wpshadow' );
		}
		
		// Check 5: Drip email notifications
		$drip_emails = get_option( 'wc_memberships_drip_email_enabled', true );
		if ( ! $drip_emails ) {
			$issues[] = __( 'Drip content notifications disabled (member experience)', 'wpshadow' );
		}
		
		// Check 6: Content availability check frequency
		$check_frequency = get_option( 'wc_memberships_drip_check_frequency', 'daily' );
		if ( 'daily' === $check_frequency && $drip_rules > 100 ) {
			$issues[] = __( 'Daily drip check with many rules (consider hourly)', 'wpshadow' );
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
				/* translators: %s: list of drip content issues */
				__( 'WooCommerce Memberships drip content has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-memberships-drip',
		);
	}
}
