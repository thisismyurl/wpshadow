<?php
/**
 * All In One Wp Security Firewall Rules Diagnostic
 *
 * All In One Wp Security Firewall Rules misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.863.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Firewall Rules Diagnostic Class
 *
 * @since 1.863.0000
 */
class Diagnostic_AllInOneWpSecurityFirewallRules extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-firewall-rules';
	protected static $title = 'All In One Wp Security Firewall Rules';
	protected static $description = 'All In One Wp Security Firewall Rules misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AIOWPSEC_VERSION' ) && ! class_exists( 'AIO_WP_Security' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify basic firewall is enabled
		$firewall_enabled = get_option( 'aiowps_enable_basic_firewall', '' );
		if ( '1' !== $firewall_enabled ) {
			$issues[] = 'basic_firewall_disabled';
		}
		
		// Check 2: Verify .htaccess firewall rules are active
		$htaccess_file = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( strpos( $htaccess_content, 'AIOWPSEC' ) === false ) {
				$issues[] = 'firewall_rules_not_in_htaccess';
			}
		} else {
			$issues[] = 'htaccess_file_missing';
		}
		
		// Check 3: Verify pingback protection is enabled
		$block_pingback = get_option( 'aiowps_block_debug_log_access', '' );
		if ( '1' !== $block_pingback ) {
			$issues[] = 'pingback_protection_disabled';
		}
		
		// Check 4: Verify SQL injection protection is active
		$prevent_sql = get_option( 'aiowps_enable_6g_firewall', '' );
		if ( '1' !== $prevent_sql ) {
			$issues[] = 'sql_injection_protection_disabled';
		}
		
		// Check 5: Verify user enumeration blocking is enabled
		$block_enum = get_option( 'aiowps_disable_author_login_in_comments', '' );
		if ( '1' !== $block_enum ) {
			$issues[] = 'user_enumeration_blocking_disabled';
		}
		
		// Check 6: Check for custom IP ranges in whitelist
		$ip_whitelist = get_option( 'aiowps_whitelist', '' );
		if ( ! empty( $ip_whitelist ) ) {
			$whitelist_count = count( explode( "\n", $ip_whitelist ) );
			if ( $whitelist_count > 50 ) {
				$issues[] = 'excessive_ip_whitelist';
			}
		}
		
		// Check 7: Verify login lockdown is configured
		$login_lockdown = get_option( 'aiowps_enable_login_lockdown', '' );
		if ( '1' !== $login_lockdown ) {
			$issues[] = 'login_lockdown_disabled';
		}
		
		// Check 8: Verify spam protection is enabled
		$spam_protection = get_option( 'aiowps_enable_comment_captcha', '' );
		if ( '1' !== $spam_protection ) {
			$issues[] = 'comment_spam_protection_disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of firewall configuration issues */
				__( 'All In One WP Security firewall has configuration issues: %s. Incomplete firewall protection leaves your site vulnerable to common attacks.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/all-in-one-wp-security-firewall-rules',
			);
		}
		
		return null;
	}
}
