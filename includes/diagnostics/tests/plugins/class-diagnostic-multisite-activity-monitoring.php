<?php
/**
 * Multisite Activity Monitoring Diagnostic
 *
 * Multisite Activity Monitoring misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.977.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Activity Monitoring Diagnostic Class
 *
 * @since 1.977.0000
 */
class Diagnostic_MultisiteActivityMonitoring extends Diagnostic_Base {

	protected static $slug = 'multisite-activity-monitoring';
	protected static $title = 'Multisite Activity Monitoring';
	protected static $description = 'Multisite Activity Monitoring misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Activity logging enabled.
		$logging = get_site_option( 'multisite_activity_logging', '1' );
		if ( '0' === $logging ) {
			$issues[] = 'activity logging disabled';
		}

		// Check 2: Log retention.
		$retention = get_site_option( 'multisite_log_retention', 0 );
		if ( 0 === $retention ) {
			$issues[] = 'no log retention policy';
		}

		// Check 3: Network admin monitoring.
		$admin_monitoring = get_site_option( 'multisite_admin_monitoring', '1' );
		if ( '0' === $admin_monitoring ) {
			$issues[] = 'admin activity not monitored';
		}

		// Check 4: Site creation tracking.
		$site_tracking = get_site_option( 'multisite_track_site_creation', '1' );
		if ( '0' === $site_tracking ) {
			$issues[] = 'site creation not tracked';
		}

		// Check 5: User switching logs.
		$user_switching = get_site_option( 'multisite_log_user_switching', '1' );
		if ( '0' === $user_switching ) {
			$issues[] = 'user switching not logged';
		}

		// Check 6: Failed login attempts.
		$failed_logins = get_site_option( 'multisite_log_failed_logins', '1' );
		if ( '0' === $failed_logins ) {
			$issues[] = 'failed logins not tracked';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Multisite monitoring issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-activity-monitoring',
			);
		}

		return null;
	}
}
