<?php
/**
 * All In One Wp Security Blacklist Diagnostic
 *
 * All In One Wp Security Blacklist misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.867.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Blacklist Diagnostic Class
 *
 * @since 1.867.0000
 */
class Diagnostic_AllInOneWpSecurityBlacklist extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-blacklist';
	protected static $title = 'All In One Wp Security Blacklist';
	protected static $description = 'All In One Wp Security Blacklist misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'AIO_WP_Security' ) && ! defined( 'AIOWPSEC_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: IP blacklist enabled.
		$blacklist_enabled = get_option( 'aiowps_enable_blacklisting', '0' );
		if ( '0' === $blacklist_enabled ) {
			$issues[] = 'IP blacklisting disabled (cannot block malicious IPs)';
		}
		
		// Check 2: User agent blacklist.
		$ua_blacklist = get_option( 'aiowps_enable_block_fake_googlebots', '0' );
		if ( '0' === $ua_blacklist ) {
			$issues[] = 'fake Googlebot blocking disabled (bots can scrape freely)';
		}
		
		// Check 3: Banned IP count.
		$banned_ips = get_option( 'aiowps_banned_ip_addresses', '' );
		if ( empty( $banned_ips ) && '1' === $blacklist_enabled ) {
			$issues[] = 'blacklist enabled but no IPs banned';
		} else {
			$ip_array = array_filter( explode( "\n", $banned_ips ) );
			if ( count( $ip_array ) > 1000 ) {
				$count = count( $ip_array );
				$issues[] = "{$count} IPs in blacklist (may slow request processing)";
			}
		}
		
		// Check 4: User agent list.
		$banned_uas = get_option( 'aiowps_banned_user_agents', '' );
		if ( empty( $banned_uas ) ) {
			$issues[] = 'no user agents banned (malicious bots not blocked)';
		}
		
		// Check 5: Lockout integration.
		$lockout_enabled = get_option( 'aiowps_enable_login_lockdown', '0' );
		if ( '0' === $lockout_enabled ) {
			$issues[] = 'login lockdown disabled (brute force attempts not automatically blocked)';
		}
		
		// Check 6: Whitelist configuration.
		$whitelist = get_option( 'aiowps_whitelist_ip_addresses', '' );
		if ( ! empty( $whitelist ) ) {
			$whitelist_array = array_filter( explode( "\n", $whitelist ) );
			if ( count( $whitelist_array ) > 50 ) {
				$count = count( $whitelist_array );
				$issues[] = "{$count} IPs whitelisted (overly permissive)";
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 70 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AIOS blacklist configuration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-blacklist',
			);
		}
		
		return null;
	}
}
