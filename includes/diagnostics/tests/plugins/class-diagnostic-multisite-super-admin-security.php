<?php
/**
 * Multisite Super Admin Security Diagnostic
 *
 * Multisite Super Admin Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.939.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Super Admin Security Diagnostic Class
 *
 * @since 1.939.0000
 */
class Diagnostic_MultisiteSuperAdminSecurity extends Diagnostic_Base {

	protected static $slug = 'multisite-super-admin-security';
	protected static $title = 'Multisite Super Admin Security';
	protected static $description = 'Multisite Super Admin Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Super admin limitation.
		$super_admins = get_super_admins();
		if ( count( $super_admins ) > 3 ) {
			$issues[] = 'too many super admins';
		}

		// Check 2: Two-factor for super admins.
		$super_2fa = get_site_option( 'require_super_admin_2fa', '0' );
		if ( '0' === $super_2fa ) {
			$issues[] = '2FA not required for super admins';
		}

		// Check 3: Super admin activity logging.
		$super_logging = get_site_option( 'log_super_admin_activity', '1' );
		if ( '0' === $super_logging ) {
			$issues[] = 'super admin activity not logged';
		}

		// Check 4: IP restriction.
		$ip_restrict = get_site_option( 'super_admin_ip_restriction', '0' );
		if ( '0' === $ip_restrict ) {
			$issues[] = 'IP restrictions not enabled';
		}

		// Check 5: Session timeout.
		$session_timeout = get_site_option( 'super_admin_session_timeout', 14400 );
		if ( $session_timeout > 28800 ) {
			$issues[] = 'session timeout too long';
		}

		// Check 6: Email notifications.
		$email_notify = get_site_option( 'super_admin_login_notify', '1' );
		if ( '0' === $email_notify ) {
			$issues[] = 'login notifications disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Super admin security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-super-admin-security',
			);
		}

		return null;
	}
}
