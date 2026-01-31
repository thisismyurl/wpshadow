<?php
/**
 * Wpmu Dev Dashboard Security Diagnostic
 *
 * Wpmu Dev Dashboard Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.950.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpmu Dev Dashboard Security Diagnostic Class
 *
 * @since 1.950.0000
 */
class Diagnostic_WpmuDevDashboardSecurity extends Diagnostic_Base {

	protected static $slug = 'wpmu-dev-dashboard-security';
	protected static $title = 'Wpmu Dev Dashboard Security';
	protected static $description = 'Wpmu Dev Dashboard Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WPMUDEV_Dashboard' ) && ! defined( 'WPMUDEV_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key security.
		$api_key = get_option( 'wpmudev_apikey', '' );
		if ( ! empty( $api_key ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'API key exposed with debug mode';
		}

		// Check 2: Auto-updates.
		$auto_update = get_option( 'wpmudev_auto_update', '1' );
		if ( '0' === $auto_update ) {
			$issues[] = 'automatic updates disabled';
		}

		// Check 3: SSL connection.
		if ( ! is_ssl() ) {
			$issues[] = 'dashboard without HTTPS';
		}

		// Check 4: Login notification.
		$login_notify = get_option( 'wpmudev_login_notification', '1' );
		if ( '0' === $login_notify ) {
			$issues[] = 'login notifications disabled';
		}

		// Check 5: Analytics tracking.
		$analytics = get_option( 'wpmudev_analytics_enabled', '1' );
		if ( '0' === $analytics ) {
			$issues[] = 'analytics disabled';
		}

		// Check 6: Security scanning.
		$security_scan = get_option( 'wpmudev_security_scan', '1' );
		if ( '0' === $security_scan ) {
			$issues[] = 'security scanning disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WPMU DEV security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpmu-dev-dashboard-security',
			);
		}

		return null;
	}
}
