<?php
/**
 * Database User Privileges Validation Diagnostic
 *
 * Validates WordPress database user has minimal required privileges.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database User Privileges Validation Class
 *
 * Checks for excessive database privileges following least-privilege principle.
 * SUPER privilege increases attack surface significantly.
 *
 * @since 1.5029.1200
 */
class Diagnostic_DB_User_Privileges extends Diagnostic_Base {

	protected static $slug        = 'database-user-privileges';
	protected static $title       = 'Database User Privileges Validation';
	protected static $description = 'Validates database user follows least-privilege principle';
	protected static $family      = 'security';

	public static function check() {
		$cache_key = 'wpshadow_db_privileges_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Get current database user grants.
		$grants = $wpdb->get_results( 'SHOW GRANTS FOR CURRENT_USER', ARRAY_N ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( empty( $grants ) || $wpdb->last_error ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$excessive_privileges = array();

		foreach ( $grants as $grant ) {
			$grant_string = $grant[0];

			// Check for dangerous privileges.
			if ( stripos( $grant_string, 'SUPER' ) !== false ) {
				$excessive_privileges[] = 'SUPER (can modify server-wide settings)';
			}
			if ( stripos( $grant_string, 'FILE' ) !== false ) {
				$excessive_privileges[] = 'FILE (can read/write files on server)';
			}
			if ( stripos( $grant_string, 'PROCESS' ) !== false ) {
				$excessive_privileges[] = 'PROCESS (can view all processes)';
			}
			if ( stripos( $grant_string, 'RELOAD' ) !== false ) {
				$excessive_privileges[] = 'RELOAD (can reload server)';
			}
		}

		if ( ! empty( $excessive_privileges ) ) {
			$threat_level = 50;
			if ( in_array( 'SUPER (can modify server-wide settings)', $excessive_privileges, true ) ) {
				$threat_level = 75;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of excessive privileges */
					__( 'Database user has %d excessive privileges. Follow least-privilege principle for hardening.', 'wpshadow' ),
					count( $excessive_privileges )
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-database-user-privileges',
				'data'         => array(
					'excessive_privileges' => $excessive_privileges,
					'recommendation'       => 'WordPress only needs: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX',
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
