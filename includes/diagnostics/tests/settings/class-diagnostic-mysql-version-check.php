<?php
/**
 * MySQL/MariaDB Version Diagnostic
 *
 * Checks MySQL/MariaDB version compatibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MySQL/MariaDB Version Diagnostic Class
 *
 * Verifies database server version is supported.
 *
 * @since 1.6093.1200
 */
class Diagnostic_MySQL_Version_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mysql-version-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'MySQL/MariaDB Version Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks database server version compatibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Run the MySQL version diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if version issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$version_string = $wpdb->get_var( 'SELECT VERSION()' );

		if ( ! $version_string ) {
			return null;
		}

		$is_mariadb = stripos( $version_string, 'MariaDB' ) !== false;
		$version_info = self::parse_version( $version_string );

		if ( ! $version_info ) {
			return null;
		}

		$major = $version_info['major'];
		$minor = $version_info['minor'];

		// Check minimum versions.
		if ( $is_mariadb ) {
			$min_version = array( 'major' => 10, 'minor' => 2 );
			$eol_versions = array( '5.5', '10.0', '10.1' );
		} else {
			$min_version = array( 'major' => 5, 'minor' => 7 );
			$eol_versions = array( '5.5', '5.6' );
		}

		$current_version = $major . '.' . $minor;

		// Check if EOL.
		if ( in_array( $current_version, $eol_versions, true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: database version */
					__( '%s is end-of-life and no longer receiving security updates. Database upgrade recommended.', 'wpshadow' ),
					$is_mariadb ? "MariaDB $current_version" : "MySQL $current_version"
				),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-database-server',
				'meta'        => array(
					'version' => $version_string,
					'type'    => $is_mariadb ? 'MariaDB' : 'MySQL',
				),
			);
		}

		// Check minimum version.
		if ( $major < $min_version['major'] || ( $major === $min_version['major'] && $minor < $min_version['minor'] ) ) {
			$min_version_str = $is_mariadb ? '10.2' : '5.7';

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current version, 2: minimum required */
					__( 'Database version %1$s is below minimum %2$s. Upgrade required for WordPress compatibility.', 'wpshadow' ),
					$current_version,
					$min_version_str
				),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-database-server',
				'meta'        => array(
					'version' => $version_string,
					'type'    => $is_mariadb ? 'MariaDB' : 'MySQL',
				),
			);
		}

		return null;
	}

	/**
	 * Parse database version string.
	 *
	 * @since 1.6093.1200
	 * @param  string $version_string Version string from database.
	 * @return array|null Parsed version or null if unable to parse.
	 */
	private static function parse_version( string $version_string ) {
		if ( preg_match( '/(\d+)\.(\d+)\.(\d+)/', $version_string, $matches ) ) {
			return array(
				'major' => (int) $matches[1],
				'minor' => (int) $matches[2],
				'patch' => (int) $matches[3],
			);
		}

		return null;
	}
}
