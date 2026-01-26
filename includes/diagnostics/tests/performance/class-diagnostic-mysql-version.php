<?php
/**
 * Diagnostic: MySQL/MariaDB Version Check
 *
 * Checks if MySQL/MariaDB version meets performance and security standards.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_MySQL_Version Class
 *
 * Detects if MySQL/MariaDB version is outdated. Database versions matter because:
 *
 * - **Security:** Old versions have known vulnerabilities
 * - **Performance:** Newer versions have significant query optimization
 * - **Features:** Modern WordPress plugins require newer DB features
 * - **Support:** Hosting providers stop supporting very old versions
 * - **Compliance:** Some security standards require current database versions
 *
 * Recommended versions:
 * - MySQL: 8.0 or higher
 * - MariaDB: 10.5 or higher (10.3+ minimum, but 10.5+ preferred)
 *
 * End-of-Life versions should be upgraded immediately.
 *
 * @since 1.2601.2200
 */
class Diagnostic_MySQL_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'mysql-version';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'MySQL/MariaDB Version';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies MySQL/MariaDB version meets performance and security standards';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Gets the database version from WordPress and checks against recommended
	 * thresholds. Distinguishes between MySQL and MariaDB versions.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if version is outdated, null if current.
	 */
	public static function check() {
		global $wpdb;

		$db_version = $wpdb->db_version();
		$is_mariadb = self::is_mariadb( $db_version );

		if ( $is_mariadb ) {
			return self::check_mariadb_version( $db_version );
		} else {
			return self::check_mysql_version( $db_version );
		}
	}

	/**
	 * Check if database is MariaDB.
	 *
	 * MariaDB includes "MariaDB" in its version string.
	 *
	 * @since  1.2601.2200
	 * @param  string $version Database version string.
	 * @return bool True if MariaDB, false if MySQL.
	 */
	private static function is_mariadb( string $version ): bool {
		return false !== stripos( $version, 'mariadb' );
	}

	/**
	 * Check MySQL version specifically.
	 *
	 * @since  1.2601.2200
	 * @param  string $version Database version string.
	 * @return array|null Finding array if outdated, null if current.
	 */
	private static function check_mysql_version( string $version ): array | null {
		// Extract just the version number (e.g., "8.0.28" from "8.0.28-0ubuntu0.20.04.1")
		preg_match( '/^\d+\.\d+\.\d+/', $version, $matches );
		$simple_version = $matches[0] ?? $version;

		// Critical: Below MySQL 5.7 (ancient)
		if ( version_compare( $simple_version, '5.7.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current MySQL version, 2: minimum recommended */
					esc_html__( 'Your MySQL version %1$s is severely outdated and unsupported. MySQL %2$s is the current recommended version. Upgrade immediately for security, performance, and WordPress compatibility.', 'wpshadow' ),
					$simple_version,
					'8.0'
				),
				'severity'           => 'high',
				'threat_level'       => 70,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-mysql-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'     => $simple_version,
					'database_type'       => 'MySQL',
					'minimum_recommended' => '8.0',
					'recommendation'      => 'Contact hosting provider to upgrade MySQL',
				),
			);
		}

		// Medium: MySQL 5.7 (old but still supported)
		if ( version_compare( $simple_version, '8.0.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current MySQL version, 2: recommended version */
					esc_html__( 'Your MySQL version %1$s is approaching end-of-life. MySQL %2$s offers significant performance improvements and security updates. Consider upgrading soon.', 'wpshadow' ),
					$simple_version,
					'8.0'
				),
				'severity'           => 'medium',
				'threat_level'       => 45,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-mysql-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'     => $simple_version,
					'database_type'       => 'MySQL',
					'minimum_recommended' => '8.0',
					'recommendation'      => 'Plan upgrade to MySQL 8.0+',
				),
			);
		}

		// All good - MySQL 8.0 or higher
		return null;
	}

	/**
	 * Check MariaDB version specifically.
	 *
	 * @since  1.2601.2200
	 * @param  string $version Database version string.
	 * @return array|null Finding array if outdated, null if current.
	 */
	private static function check_mariadb_version( string $version ): array | null {
		// Extract MariaDB version (appears before "MariaDB" string)
		preg_match( '/^(\d+\.\d+\.\d+)/', $version, $matches );
		$simple_version = $matches[1] ?? $version;

		// Critical: Below MariaDB 10.1 (ancient)
		if ( version_compare( $simple_version, '10.1.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current MariaDB version, 2: recommended version */
					esc_html__( 'Your MariaDB version %1$s is severely outdated and unsupported. MariaDB %2$s or higher is recommended. Upgrade immediately for security and WordPress compatibility.', 'wpshadow' ),
					$simple_version,
					'10.5'
				),
				'severity'           => 'high',
				'threat_level'       => 70,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-mysql-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'     => $simple_version,
					'database_type'       => 'MariaDB',
					'minimum_recommended' => '10.5',
					'recommendation'      => 'Contact hosting provider to upgrade MariaDB',
				),
			);
		}

		// Medium: MariaDB 10.1-10.2 (acceptable but getting old)
		if ( version_compare( $simple_version, '10.3.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current MariaDB version, 2: recommended version */
					esc_html__( 'Your MariaDB version %1$s is acceptable, but %2$s or higher offers better performance. Consider upgrading at your next maintenance window.', 'wpshadow' ),
					$simple_version,
					'10.5'
				),
				'severity'           => 'medium',
				'threat_level'       => 45,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-mysql-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'     => $simple_version,
					'database_type'       => 'MariaDB',
					'minimum_recommended' => '10.5',
					'recommendation'      => 'Plan upgrade to MariaDB 10.5+',
				),
			);
		}

		// All good - MariaDB 10.3 or higher
		return null;
	}
}
