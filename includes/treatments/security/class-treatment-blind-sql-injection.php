<?php
/**
 * Blind SQL Injection Treatment
 *
 * Detects potential blind SQL injection vulnerabilities in
 * themes, plugins, and custom code.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blind SQL Injection Treatment Class
 *
 * Checks for:
 * - Use of $wpdb->query() without $wpdb->prepare()
 * - Time-based SQL injection patterns (SLEEP, BENCHMARK)
 * - Boolean-based blind SQLi patterns
 * - Error suppression that could hide SQLi attempts
 * - User input concatenation in SQL queries
 *
 * Blind SQL injection is particularly dangerous because it
 * doesn't display errors, making it harder to detect. Attackers
 * can extract entire databases one character at a time using
 * time-based or boolean-based techniques.
 *
 * @since 1.2033.2102
 */
class Treatment_Blind_SQL_Injection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'blind-sql-injection';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Blind SQL Injection Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects potential blind SQL injection vulnerabilities in code';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans active theme and plugins for patterns indicating
	 * potential blind SQL injection vulnerabilities.
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Blind_SQL_Injection' );
	}

	/**
	 * Scan a directory for PHP files.
	 *
	 * @since  1.2033.2102
	 * @param  string $dir Directory to scan.
	 * @param  int    $limit Maximum number of files to scan.
	 * @return array Array of file paths.
	 */
	private static function scan_directory_for_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}

			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}

	/**
	 * Scan a file for dangerous patterns.
	 *
	 * @since  1.2033.2102
	 * @param  string $file File path.
	 * @param  array  $patterns Array of regex patterns to search for.
	 * @return array Array of violations found.
	 */
	private static function scan_file_for_patterns( $file, $patterns ) {
		$violations = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $violations;
		}

		foreach ( $patterns as $pattern => $description ) {
			if ( preg_match( $pattern, $content ) ) {
				$violations[] = $description;
			}
		}

		return $violations;
	}
}
