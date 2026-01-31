<?php
/**
 * Multisite User Management Security Diagnostic
 *
 * Multisite User Management Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.943.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite User Management Security Diagnostic Class
 *
 * @since 1.943.0000
 */
class Diagnostic_MultisiteUserManagementSecurity extends Diagnostic_Base {

	protected static $slug = 'multisite-user-management-security';
	protected static $title = 'Multisite User Management Security';
	protected static $description = 'Multisite User Management Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: User enumeration protection
		$prevent_enumeration = get_site_option( 'ms_prevent_user_enumeration', false );
		if ( ! $prevent_enumeration ) {
			$issues[] = __( 'User enumeration not prevented (cross-site user discovery)', 'wpshadow' );
		}
		
		// Check 2: Super admin count
		$super_admins = get_super_admins();
		if ( count( $super_admins ) > 5 ) {
			$issues[] = sprintf( __( '%d super admins (excessive privileges)', 'wpshadow' ), count( $super_admins ) );
		}
		
		// Check 3: Add user capability restricted
		$restrict_add_users = get_site_option( 'add_new_users', false );
		if ( ! $restrict_add_users ) {
			$issues[] = __( 'Site admins can add existing users (privilege escalation risk)', 'wpshadow' );
		}
		
		// Check 4: User meta access control
		$protect_meta = get_site_option( 'ms_protect_user_meta', false );
		if ( ! $protect_meta ) {
			$issues[] = __( 'User meta not protected across sites (data leakage)', 'wpshadow' );
		}
		
		// Check 5: Network-wide role assignments
		$network_roles = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta}
			 WHERE meta_key LIKE '%capabilities%'
			 GROUP BY user_id HAVING COUNT(*) > 10"
		);
		
		if ( $network_roles > 0 ) {
			$issues[] = sprintf( __( '%d users with roles on many sites (review permissions)', 'wpshadow' ), $network_roles );
		}
		
		// Check 6: User activity logging
		$log_user_changes = get_site_option( 'ms_log_user_changes', false );
		if ( ! $log_user_changes ) {
			$issues[] = __( 'User management changes not logged (no audit trail)', 'wpshadow' );
		}
		
		// Check 7: User deletion restrictions
		$protect_deletion = get_site_option( 'ms_prevent_user_deletion', false );
		if ( ! $protect_deletion ) {
			$issues[] = __( 'No safeguards for user deletion (accidental data loss)', 'wpshadow' );
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
				/* translators: %s: list of security issues */
				__( 'Multisite user management has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-user-management-security',
		);
	}
}
