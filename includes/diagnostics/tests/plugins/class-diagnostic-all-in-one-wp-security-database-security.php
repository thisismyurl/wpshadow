<?php
/**
 * All In One Wp Security Database Security Diagnostic
 *
 * All In One Wp Security Database Security misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.864.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security Database Security Diagnostic Class
 *
 * @since 1.864.0000
 */
class Diagnostic_AllInOneWpSecurityDatabaseSecurity extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-database-security';
	protected static $title = 'All In One Wp Security Database Security';
	protected static $description = 'All In One Wp Security Database Security misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'AIO_WP_Security' ) && ! defined( 'AIOWPSEC_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Database prefix changed.
		global $wpdb;
		if ( 'wp_' === $wpdb->prefix ) {
			$issues[] = 'using default wp_ prefix (makes SQL injection easier)';
		}
		
		// Check 2: DB backup scheduled.
		$db_backup = get_option( 'aiowps_enable_automated_backups', '0' );
		if ( '0' === $db_backup ) {
			$issues[] = 'automated database backups not configured';
		}
		
		// Check 3: Database credentials in wp-config.
		$config_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $config_path ) ) {
			$perms = fileperms( $config_path );
			$world_readable = ( $perms & 0x0004 );
			if ( $world_readable ) {
				$issues[] = 'wp-config.php is world-readable (database credentials exposed)';
			}
		}
		
		// Check 4: DB optimization enabled.
		$db_optimize = get_option( 'aiowps_enable_db_optimization', '0' );
		if ( '0' === $db_optimize ) {
			$issues[] = 'database optimization disabled (tables may have overhead)';
		}
		
		// Check 5: Exposed SQL errors.
		if ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) {
			$issues[] = 'SQL errors displayed to users (information disclosure)';
		}
		
		// Check 6: Direct database access.
		$restrict_db_access = get_option( 'aiowps_enable_db_access_restriction', '0' );
		if ( '0' === $restrict_db_access ) {
			$issues[] = 'direct database access not restricted (phpMyAdmin exposure risk)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'AIOS database security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-database-security',
			);
		}
		
		return null;
	}
}
