<?php
/**
 * Wordfence Firewall Rules Diagnostic
 *
 * Analyzes firewall rule effectiveness.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Firewall Rules Class
 *
 * Validates firewall rule configuration and coverage.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_Firewall extends Diagnostic_Base {

	protected static $slug        = 'wordfence-firewall';
	protected static $title       = 'Wordfence Firewall Rules';
	protected static $description = 'Analyzes firewall rule effectiveness';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_firewall';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$issues = array();

		// Check WAF status.
		$waf_status = wfConfig::get( 'wafStatus', 'disabled' );
		if ( 'disabled' === $waf_status ) {
			$issues[] = array(
				'issue' => 'Web Application Firewall is disabled',
				'severity' => 'critical',
				'recommendation' => 'Enable WAF for maximum protection',
			);
		}

		// Check firewall rule count.
		$rules_table = $wpdb->prefix . 'wfBlockedIPLog';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$rules_table}'" ) ) {
			$blocked_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$rules_table}" );
			if ( $blocked_count > 10000 ) {
				$issues[] = array(
					'issue' => sprintf( '%d blocked IPs - database may be bloated', $blocked_count ),
					'severity' => 'medium',
					'recommendation' => 'Clean up old blocked IP entries',
				);
			}
		}

		// Check rate limiting.
		$rate_limit = wfConfig::get( 'maxGlobalRequests', 0 );
		if ( ! $rate_limit ) {
			$issues[] = array(
				'issue' => 'Rate limiting is disabled',
				'severity' => 'medium',
				'recommendation' => 'Enable rate limiting to prevent DDoS',
			);
		}

		// Check brute force protection.
		$bf_protection = wfConfig::get( 'loginSecurityEnabled', 0 );
		if ( ! $bf_protection ) {
			$issues[] = array(
				'issue' => 'Brute force protection is disabled',
				'severity' => 'high',
				'recommendation' => 'Enable login security features',
			);
		}

		if ( ! empty( $issues ) ) {
			$critical_count = count( array_filter( $issues, function( $i ) {
				return 'critical' === $i['severity'];
			} ) );

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d firewall configuration issues detected. Site may be vulnerable.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $critical_count > 0 ? 'critical' : 'high',
				'threat_level' => $critical_count > 0 ? 70 : 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-firewall',
				'data'         => array(
					'firewall_issues' => $issues,
					'total_issues' => count( $issues ),
					'critical_issues' => $critical_count,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
