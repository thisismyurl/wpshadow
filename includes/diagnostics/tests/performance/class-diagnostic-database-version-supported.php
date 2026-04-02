<?php
/**
 * Database Version Supported Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 72.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Version Supported Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Version_Supported extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'database-version-supported';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Database Version Supported';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Database Version Supported. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Query DB server version and compare minimum supported ranges.
	 *
	 * TODO Fix Plan:
	 * Fix by planning DB engine upgrade.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$raw_version = Server_Env::get_db_version();

		if ( '' === $raw_version ) {
			return null; // Cannot determine version.
		}

		// Extract the leading numeric portion (e.g. '8.0.32' from '8.0.32' or '10.6.12-MariaDB').
		preg_match( '/^([\d.]+)/', $raw_version, $matches );
		$clean_version = $matches[1] ?? $raw_version;

		$is_mariadb = Server_Env::is_mariadb();

		if ( $is_mariadb ) {
			// MariaDB 10.4+ is the current minimum for full JSON, InnoDB, and security support.
			$min_version = '10.4.0';
			$recommended = '10.6.0';
		} else {
			// MySQL 5.7 EOL January 2024; MySQL 8.0 is current.
			$min_version = '5.7.0';
			$recommended = '8.0.0';
		}

		if ( version_compare( $clean_version, $recommended, '>=' ) ) {
			return null;
		}

		$below_minimum = version_compare( $clean_version, $min_version, '<' );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: db type, 2: current version, 3: recommended version */
				__( 'Your %1$s version is %2$s. The recommended minimum is %3$s. Older database versions miss performance improvements, bug fixes, and security patches. Contact your hosting provider to upgrade.', 'wpshadow' ),
				$is_mariadb ? 'MariaDB' : 'MySQL',
				$clean_version,
				$recommended
			),
			'severity'     => $below_minimum ? 'high' : 'medium',
			'threat_level' => $below_minimum ? 65 : 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-version',
			'details'      => array(
				'db_type'          => $is_mariadb ? 'MariaDB' : 'MySQL',
				'current_version'  => $clean_version,
				'raw_version'      => $raw_version,
				'recommended'      => $recommended,
				'minimum'          => $min_version,
			),
		);
	}
}
