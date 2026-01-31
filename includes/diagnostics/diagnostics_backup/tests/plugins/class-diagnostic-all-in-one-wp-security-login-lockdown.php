<?php
/**
 * All In One Wp Security Login Lockdown Diagnostic
 *
 * All In One Wp Security Login Lockdown misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.862.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Login Lockdown Diagnostic Class
 *
 * @since 1.862.0000
 */
class Diagnostic_AllInOneWpSecurityLoginLockdown extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-login-lockdown';
	protected static $title = 'All In One Wp Security Login Lockdown';
	protected static $description = 'All In One Wp Security Login Lockdown misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AIOWPS_DIR' ) && ! class_exists( 'AIOWPS_User_Login_Activity' ) ) {
			return null;
		}
		$issues = array();
		$login_attempts = get_option( 'aiowps_max_login_attempts', 5 );
		if ( $login_attempts > 10 ) { $issues[] = "login attempts limit too high ({$login_attempts})"; }
		$lockdown_enabled = get_option( 'aiowps_enable_login_lockdown', 0 );
		if ( '1' !== $lockdown_enabled ) { $issues[] = 'login lockdown not enabled'; }
		$lockdown_duration = get_option( 'aiowps_lockdown_duration', 60 );
		if ( $lockdown_duration < 30 ) { $issues[] = "lockdown duration too short ({$lockdown_duration} minutes)"; }
		$notify_email = get_option( 'aiowps_notify_on_lockdown', 0 );
		if ( '1' !== $notify_email ) { $issues[] = 'lockdown notification disabled'; }
		$ip_whitelist = get_option( 'aiowps_login_ip_whitelist', array() );
		if ( empty( $ip_whitelist ) ) { $issues[] = 'no IP whitelist configured'; }
		$custom_login_page = get_option( 'aiowps_custom_login_page', '' );
		if ( empty( $custom_login_page ) ) { $issues[] = 'default login page still exposed'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 90, 70 + ( count( $issues ) * 3 ) ) ), 'threat_level' => min( 90, 70 + ( count( $issues ) * 3 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/all-in-one-wp-security-login-lockdown' );
		}
		return null;
	}
}
