<?php
/**
 * Ithemes Security Brute Force Diagnostic
 *
 * Ithemes Security Brute Force misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.856.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security Brute Force Diagnostic Class
 *
 * @since 1.856.0000
 */
class Diagnostic_IthemesSecurityBruteForce extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-brute-force';
	protected static $title = 'Ithemes Security Brute Force';
	protected static $description = 'Ithemes Security Brute Force misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) && ! class_exists( 'ITSEC_Core' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify brute force protection is enabled
		$brute_force_enabled = get_site_option( 'itsec_local_file_config_enabled', false );
		if ( ! $brute_force_enabled ) {
			$issues[] = 'brute_force_protection_disabled';
		}
		
		// Check 2: Verify login attempt limits are configured
		$max_attempts = get_site_option( 'itsec_login_max_attempts', 10 );
		if ( $max_attempts > 10 ) {
			$issues[] = 'max_login_attempts_too_high';
		}
		
		// Check 3: Verify lockout period is reasonable
		$lockout_period = get_site_option( 'itsec_login_lockout_minutes', 15 );
		if ( $lockout_period < 15 ) {
			$issues[] = 'lockout_period_too_short';
		}
		
		// Check 4: Verify whitelist is not too permissive
		$whitelist = get_site_option( 'itsec_whitelist', array() );
		if ( is_array( $whitelist ) && count( $whitelist ) > 20 ) {
			$issues[] = 'whitelist_too_permissive';
		}
		
		// Check 5: Verify network brute force protection is enabled
		$network_brute_force = get_site_option( 'itsec_network_brute_force_enabled', false );
		if ( ! $network_brute_force ) {
			$issues[] = 'network_brute_force_disabled';
		}
		
		// Check 6: Check for recent lockouts indicating active attack
		global $wpdb;
		$lockout_table = $wpdb->prefix . 'itsec_lockouts';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$lockout_table}'" ) === $lockout_table ) {
			$recent_lockouts = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$lockout_table} 
					WHERE lockout_expire_gmt > %s",
					gmdate( 'Y-m-d H:i:s', time() - 3600 )
				)
			);
			
			if ( $recent_lockouts > 50 ) {
				$issues[] = 'high_number_recent_lockouts';
			}
		}
		
		// Check 7: Verify email notifications are configured
		$notify_admins = get_site_option( 'itsec_lockout_notify_admins', false );
		if ( ! $notify_admins ) {
			$issues[] = 'lockout_notifications_disabled';
		}
		
		// Check 8: Verify banned users list is maintained
		$banned_users = get_site_option( 'itsec_banned_users', array() );
		if ( empty( $banned_users ) ) {
			// Not necessarily bad, but worth noting
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of brute force protection issues */
				__( 'iThemes Security brute force protection has configuration issues: %s. Weak brute force protection allows attackers to attempt credential stuffing and password guessing attacks.', 'wpshadow' ),
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
				'kb_link'      => 'https://wpshadow.com/kb/ithemes-security-brute-force',
			);
		}
		
		return null;
	}
}
