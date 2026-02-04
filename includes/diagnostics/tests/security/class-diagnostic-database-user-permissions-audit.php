<?php
/**
 * Database User Permissions Audit Diagnostic
 *
 * Audits MySQL user permissions to enforce least-privilege principle.
 * Detects overly permissive database users that could be security risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database User Permissions Audit Class
 *
 * Verifies database user has only necessary permissions for WordPress operation.
 * Flags dangerous permissions like SUPER, FILE, PROCESS, SHUTDOWN, etc.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Database_User_Permissions_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-user-permissions-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database User Permissions Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Audits MySQL user permissions for security best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Permissions WordPress requires for normal operation.
	 *
	 * @var array
	 */
	const REQUIRED_PERMISSIONS = array(
		'SELECT',
		'INSERT',
		'UPDATE',
		'DELETE',
		'CREATE',
		'DROP',
		'ALTER',
		'INDEX',
		'CREATE TEMPORARY TABLES',
		'LOCK TABLES',
	);

	/**
	 * Dangerous permissions that WordPress should NOT have.
	 *
	 * @var array
	 */
	const DANGEROUS_PERMISSIONS = array(
		'SUPER',
		'FILE',
		'PROCESS',
		'SHUTDOWN',
		'RELOAD',
		'GRANT OPTION',
		'CREATE USER',
		'REPLICATION SLAVE',
		'REPLICATION CLIENT',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes database user permissions using SHOW GRANTS and compares
	 * against WordPress requirements and security best practices.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if permission issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get current database user grants.
		$grants = $wpdb->get_results( 'SHOW GRANTS FOR CURRENT_USER()', ARRAY_N );

		if ( empty( $grants ) ) {
			// Can't check permissions.
			return null;
		}

		$all_permissions = array();
		$dangerous_found = array();
		$has_all_privileges = false;

		// Parse GRANT statements.
		foreach ( $grants as $grant ) {
			$grant_string = $grant[0];

			// Check for ALL PRIVILEGES.
			if ( stripos( $grant_string, 'ALL PRIVILEGES' ) !== false ) {
				$has_all_privileges = true;
				$issues[] = __( 'Database user has ALL PRIVILEGES grant, which violates least-privilege principle.', 'wpshadow' );
			}

			// Extract individual permissions.
			if ( preg_match( '/GRANT\s+(.+?)\s+ON/i', $grant_string, $matches ) ) {
				$permissions_str = $matches[1];
				$permissions = array_map( 'trim', explode( ',', $permissions_str ) );

				foreach ( $permissions as $perm ) {
					// Clean permission name.
					$perm = strtoupper( trim( $perm ) );
					$all_permissions[] = $perm;

					// Check for dangerous permissions.
					foreach ( self::DANGEROUS_PERMISSIONS as $dangerous ) {
						if ( stripos( $perm, $dangerous ) !== false ) {
							$dangerous_found[] = $dangerous;
						}
					}
				}
			}

			// Check for wildcard database access.
			if ( preg_match( '/ON\s+`?\*`?\.`?\*`?/i', $grant_string ) ) {
				$issues[] = __( 'Database user has access to ALL databases (*.*), which is excessive for WordPress.', 'wpshadow' );
			}
		}

		// Report dangerous permissions.
		if ( ! empty( $dangerous_found ) ) {
			$dangerous_found = array_unique( $dangerous_found );

			$issues[] = sprintf(
				/* translators: %s: comma-separated list of dangerous permissions */
				_n(
					'Database user has dangerous permission: %s',
					'Database user has dangerous permissions: %s',
					count( $dangerous_found ),
					'wpshadow'
				),
				implode( ', ', $dangerous_found )
			);
		}

		// Check for remote access (not localhost).
		$current_user_host = $wpdb->get_var( "SELECT SUBSTRING_INDEX(CURRENT_USER(), '@', -1) AS host" );

		if ( $current_user_host && ! in_array( $current_user_host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			// Remove quotes if present.
			$current_user_host = trim( $current_user_host, "'" );

			if ( '%' === $current_user_host || false !== strpos( $current_user_host, '%' ) ) {
				$issues[] = sprintf(
					/* translators: %s: database host pattern */
					__( 'Database user allows connections from any host (%s), which is a security risk.', 'wpshadow' ),
					$current_user_host
				);
			}
		}

		// Check if any required permissions are missing (rare but possible).
		$missing_permissions = array();

		foreach ( self::REQUIRED_PERMISSIONS as $required ) {
			$found = false;

			if ( $has_all_privileges ) {
				$found = true;
			} else {
				foreach ( $all_permissions as $granted ) {
					if ( stripos( $granted, $required ) !== false ) {
						$found = true;
						break;
					}
				}
			}

			if ( ! $found ) {
				$missing_permissions[] = $required;
			}
		}

		if ( ! empty( $missing_permissions ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing permissions */
				_n(
					'Database user is missing required permission: %s',
					'Database user is missing required permissions: %s',
					count( $missing_permissions ),
					'wpshadow'
				),
				implode( ', ', $missing_permissions )
			);
		}

		// Check for specific database access.
		$has_specific_database_grant = false;

		foreach ( $grants as $grant ) {
			$grant_string = $grant[0];

			if ( preg_match( '/ON\s+`?' . preg_quote( DB_NAME, '/' ) . '`?\./i', $grant_string ) ) {
				$has_specific_database_grant = true;
			}
		}

		if ( ! $has_specific_database_grant && ! $has_all_privileges ) {
			$issues[] = sprintf(
				/* translators: %s: database name */
				__( 'Database user does not have explicit grants on database "%s", which may cause issues.', 'wpshadow' ),
				DB_NAME
			);
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( ' ', $issues ),
			'severity'    => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/database-user-permissions',
			'context'     => array(
				'issues'                => $issues,
				'current_user'          => DB_USER,
				'current_host'          => $current_user_host ?? 'unknown',
				'dangerous_permissions' => $dangerous_found,
				'missing_permissions'  => $missing_permissions,
				'why'                   => __(
					'Over-privileged database users are a critical security liability. WordPress requires only: SELECT, INSERT, UPDATE, DELETE, ' .
					'CREATE, DROP, ALTER, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES. Any additional privilege is an attack surface. SUPER privilege ' .
					'allows killing queries and changing server settings. FILE privilege enables reading system files (LOAD_FILE()) and writing webshells (INTO OUTFILE). ' .
					'GRANT OPTION allows creating backdoor users. Wildcard database access (*.*) means if WordPress is compromised, attacker can access all databases ' .
					'on the server (other hosted sites). Remote host access (not localhost) multiplies the risk by allowing direct database connections.',
					'wpshadow'
				),
				'recommendation'        => __(
					'Restrict database user to localhost connection only (host should be localhost or 127.0.0.1). Remove all dangerous permissions ' .
					'(SUPER, FILE, PROCESS, GRANT OPTION). Grant only essential permissions for WordPress operation. Use command: ' .
					'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES ON database_name.* TO wp_user@localhost. ' .
					'Never use wildcard database access. Create separate read-only user for backups if needed. Regularly audit user permissions. ' .
					'Work with hosting provider to enforce least-privilege by default.',
					'wpshadow'
				),
			),
			'details'     => array(
				'current_user'         => DB_USER,
				'current_host'         => $current_user_host ?? 'unknown',
				'has_all_privileges'   => $has_all_privileges,
				'dangerous_permissions' => $dangerous_found,
				'missing_permissions'  => $missing_permissions,
				'grants_count'         => count( $grants ),
				'raw_grants'           => array_column( $grants, 0 ),
			),
		);

		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'database-hardening',
			'database-permissions-guide'
		);

		return $finding;
	}
}
