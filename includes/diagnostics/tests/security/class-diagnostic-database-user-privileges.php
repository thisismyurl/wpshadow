<?php
/**
 * Database User Privileges Validation Diagnostic
 *
 * Validates that WordPress database user has minimal required privileges
 * and does not have excessive permissions like SUPER.
 *
 * @since   1.2802.1430
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_User_Privileges Class
 *
 * Checks database user privileges against least-privilege principle.
 * Flags excessive permissions like SUPER, FILE, PROCESS, RELOAD.
 *
 * @since 1.2802.1430
 */
class Diagnostic_Database_User_Privileges extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-user-privileges';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database User Privileges Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates least-privilege principle for database user';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Excessive privileges that should not be granted
	 *
	 * @var array
	 */
	const EXCESSIVE_PRIVILEGES = array(
		'SUPER'   => 'critical',
		'FILE'    => 'high',
		'PROCESS' => 'high',
		'RELOAD'  => 'high',
	);

	/**
	 * Required minimum privileges for WordPress
	 *
	 * @var array
	 */
	const REQUIRED_PRIVILEGES = array(
		'SELECT',
		'INSERT',
		'UPDATE',
		'DELETE',
		'CREATE',
		'DROP',
		'INDEX',
		'ALTER',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2802.1430
	 * @return array|null Finding array if excessive privileges found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Step 1: Early bailout - check if we can access database
		if ( ! self::should_run_check() ) {
			return null;
		}

		// Step 2: Get database user privileges
		$privileges = self::get_database_privileges();

		// Step 3: Handle errors gracefully
		if ( is_wp_error( $privileges ) ) {
			// Permission denied or other database error - don't report as finding
			return null;
		}

		// Step 4: Analyze privileges for excessive permissions
		$analysis = self::analyze_privileges( $privileges );

		// Step 5: If no excessive privileges, return null
		if ( empty( $analysis['excessive'] ) ) {
			return null;
		}

		// Step 6: Return comprehensive finding
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of excessive privileges */
				__( '%d excessive database privileges detected. Database user has more permissions than needed for WordPress operation (principle of least privilege violation).', 'wpshadow' ),
				count( $analysis['excessive'] )
			),
			'severity'     => self::get_severity_for_privileges( $analysis['excessive'] ),
			'threat_level' => self::get_threat_level_for_privileges( $analysis['excessive'] ),
			'auto_fixable' => false, // Requires hosting provider
			'kb_link'      => 'https://wpshadow.com/kb/security-database-user-privileges',
			'family'       => self::$family,
			'meta'         => array(
				'excessive_privileges'  => implode( ', ', array_keys( $analysis['excessive'] ) ),
				'has_super'             => in_array( 'SUPER', array_keys( $analysis['excessive'] ), true ),
				'required_privileges'   => implode( ', ', self::REQUIRED_PRIVILEGES ),
				'database_user'         => self::get_sanitized_db_user(),
			),
			'details'      => array(
				'why_least_privilege_matters' => array(
					__( 'SUPER privilege = full database server control (create/drop any database, kill any process)', 'wpshadow' ),
					__( 'FILE privilege = read/write files anywhere on server (potential data theft)', 'wpshadow' ),
					__( 'PROCESS privilege = view all running processes (info disclosure)', 'wpshadow' ),
					__( 'RELOAD privilege = flush caches, reload grant tables (service disruption)', 'wpshadow' ),
					__( 'Principle of least privilege: grant only minimum required permissions', 'wpshadow' ),
				),
				'excessive_privileges_detail' => self::format_excessive_privileges( $analysis['excessive'] ),
				'required_privileges_list'    => array(
					'SELECT'  => __( 'Read data from database tables', 'wpshadow' ),
					'INSERT'  => __( 'Add new records to tables', 'wpshadow' ),
					'UPDATE'  => __( 'Modify existing records', 'wpshadow' ),
					'DELETE'  => __( 'Remove records from tables', 'wpshadow' ),
					'CREATE'  => __( 'Create new tables (plugin activation)', 'wpshadow' ),
					'DROP'    => __( 'Remove tables (plugin deactivation)', 'wpshadow' ),
					'INDEX'   => __( 'Create/remove indexes (performance)', 'wpshadow' ),
					'ALTER'   => __( 'Modify table structure (upgrades)', 'wpshadow' ),
				),
				'remediation_steps'           => array(
					'Step 1: Contact Hosting Provider' => __( 'Database privileges can only be modified by hosting provider or database administrator', 'wpshadow' ),
					'Step 2: Request Privilege Review' => __( 'Ask to review current database user grants and remove excessive privileges', 'wpshadow' ),
					'Step 3: Specify Required Only'    => __( 'Request ONLY: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER on WordPress database', 'wpshadow' ),
					'Step 4: Test Site Functionality'  => __( 'After changes, verify WordPress functions normally (post, plugin activation, updates)', 'wpshadow' ),
					'Step 5: Document Changes'         => __( 'Keep record of privilege changes for compliance/audit', 'wpshadow' ),
				),
				'security_audit_compliance'   => array(
					'PCI DSS 2.2.4' => __( 'Configure system security parameters to prevent misuse', 'wpshadow' ),
					'CIS MySQL'     => __( 'Remove unnecessary privileges from application accounts', 'wpshadow' ),
					'OWASP'         => __( 'Principle of least privilege for database accounts', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if diagnostic should run.
	 *
	 * @since  1.2802.1430
	 * @return bool True if check should run, false otherwise.
	 */
	private static function should_run_check() {
		global $wpdb;

		// Only run if database connection exists
		return isset( $wpdb ) && $wpdb instanceof \wpdb;
	}

	/**
	 * Get database user privileges.
	 *
	 * @since  1.2802.1430
	 * @return array|\WP_Error Array of privileges or WP_Error on failure.
	 */
	private static function get_database_privileges() {
		global $wpdb;

		// Suppress errors to handle permission denied gracefully
		$wpdb->suppress_errors( true );

		// Execute SHOW GRANTS (read-only, safe)
		$grants = $wpdb->get_results( 'SHOW GRANTS', ARRAY_N );

		$wpdb->suppress_errors( false );

		// Handle errors
		if ( $wpdb->last_error ) {
			return new \WP_Error( 'db_error', $wpdb->last_error );
		}

		if ( empty( $grants ) ) {
			return new \WP_Error( 'no_grants', 'No grants returned' );
		}

		// Parse grants
		$privileges = array();
		foreach ( $grants as $grant ) {
			if ( isset( $grant[0] ) ) {
				$parsed = self::parse_grant_string( $grant[0] );
				$privileges = array_merge( $privileges, $parsed );
			}
		}

		return array_unique( $privileges );
	}

	/**
	 * Parse GRANT statement to extract privileges.
	 *
	 * Handles both MySQL and MariaDB GRANT formats.
	 *
	 * @since  1.2802.1430
	 * @param  string $grant_string GRANT statement.
	 * @return array Array of privilege names.
	 */
	private static function parse_grant_string( $grant_string ) {
		$privileges = array();

		// Match "GRANT privilege1, privilege2, ... ON"
		// or "GRANT ALL PRIVILEGES ON"
		if ( preg_match( '/GRANT\s+(.+?)\s+ON/i', $grant_string, $matches ) ) {
			$grant_part = $matches[1];

			// Handle "ALL PRIVILEGES"
			if ( stripos( $grant_part, 'ALL' ) !== false ) {
				// ALL includes all possible privileges
				$privileges = array_merge(
					self::REQUIRED_PRIVILEGES,
					array_keys( self::EXCESSIVE_PRIVILEGES )
				);
			} else {
				// Parse individual privileges
				$parts = explode( ',', $grant_part );
				foreach ( $parts as $part ) {
					$privilege = trim( $part );
					// Remove any parentheses (e.g., "SELECT (col1, col2)")
					$privilege = preg_replace( '/\s*\(.*?\)/', '', $privilege );
					$privilege = strtoupper( $privilege );
					if ( $privilege ) {
						$privileges[] = $privilege;
					}
				}
			}
		}

		return $privileges;
	}

	/**
	 * Analyze privileges to find excessive permissions.
	 *
	 * @since  1.2802.1430
	 * @param  array $privileges Array of privilege names.
	 * @return array {
	 *     Analysis results.
	 *
	 *     @type array $excessive Excessive privileges found.
	 *     @type array $required Required privileges present.
	 *     @type array $missing Missing required privileges.
	 * }
	 */
	private static function analyze_privileges( $privileges ) {
		$excessive = array();
		$required  = array();
		$missing   = array();

		// Check for excessive privileges
		foreach ( self::EXCESSIVE_PRIVILEGES as $priv => $severity ) {
			if ( in_array( $priv, $privileges, true ) ) {
				$excessive[ $priv ] = $severity;
			}
		}

		// Check required privileges
		foreach ( self::REQUIRED_PRIVILEGES as $priv ) {
			if ( in_array( $priv, $privileges, true ) ) {
				$required[] = $priv;
			} else {
				$missing[] = $priv;
			}
		}

		return array(
			'excessive' => $excessive,
			'required'  => $required,
			'missing'   => $missing,
		);
	}

	/**
	 * Get severity level based on excessive privileges.
	 *
	 * @since  1.2802.1430
	 * @param  array $excessive Excessive privileges array.
	 * @return string Severity level.
	 */
	private static function get_severity_for_privileges( $excessive ) {
		if ( isset( $excessive['SUPER'] ) ) {
			return 'critical';
		}
		return 'high';
	}

	/**
	 * Get threat level (0-100) based on excessive privileges.
	 *
	 * @since  1.2802.1430
	 * @param  array $excessive Excessive privileges array.
	 * @return int Threat level 0-100.
	 */
	private static function get_threat_level_for_privileges( $excessive ) {
		if ( isset( $excessive['SUPER'] ) ) {
			return 80; // Critical: SUPER privilege
		} elseif ( count( $excessive ) >= 2 ) {
			return 65; // High: Multiple excessive privileges
		}
		return 50; // High: Single excessive privilege
	}

	/**
	 * Format excessive privileges for display.
	 *
	 * @since  1.2802.1430
	 * @param  array $excessive Excessive privileges array.
	 * @return array Formatted privileges list.
	 */
	private static function format_excessive_privileges( $excessive ) {
		$formatted = array();

		$descriptions = array(
			'SUPER'   => __( 'Full database server control. Can create/drop any database, kill processes, change grants. EXTREMELY DANGEROUS.', 'wpshadow' ),
			'FILE'    => __( 'Read/write files anywhere on server. Can steal data, upload malware. VERY DANGEROUS.', 'wpshadow' ),
			'PROCESS' => __( 'View all database processes. Information disclosure risk.', 'wpshadow' ),
			'RELOAD'  => __( 'Flush tables, reload privileges. Service disruption risk.', 'wpshadow' ),
		);

		foreach ( $excessive as $priv => $severity ) {
			$formatted[ $priv ] = array(
				'severity'    => ucfirst( $severity ),
				'description' => $descriptions[ $priv ] ?? __( 'Excessive privilege for WordPress operation', 'wpshadow' ),
			);
		}

		return $formatted;
	}

	/**
	 * Get sanitized database username for display.
	 *
	 * @since  1.2802.1430
	 * @return string Sanitized username.
	 */
	private static function get_sanitized_db_user() {
		if ( ! defined( 'DB_USER' ) ) {
			return 'unknown';
		}

		// Sanitize for display (remove sensitive info)
		$user = DB_USER;
		// Truncate if too long
		if ( strlen( $user ) > 32 ) {
			$user = substr( $user, 0, 29 ) . '...';
		}

		return $user;
	}

	/**
	 * Set test privileges for testing purposes.
	 *
	 * This allows tests to inject mock privilege data without database access.
	 *
	 * @since  1.2802.1430
	 * @param  array $privileges Mock privilege array.
	 * @return void
	 */
	public static function set_test_privileges( $privileges ) {
		global $wpdb;
		$wpdb->test_privileges = $privileges;
	}

	/**
	 * Clear test privileges.
	 *
	 * @since  1.2802.1430
	 * @return void
	 */
	public static function clear_test_privileges() {
		global $wpdb;
		unset( $wpdb->test_privileges );
	}
}
