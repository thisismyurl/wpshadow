<?php
/**
 * Second-Order SQL Injection Treatment
 *
 * Detects second-order SQL injection vulnerabilities where malicious
 * input is stored and later used in unsafe SQL queries.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2105
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Second-Order SQL Injection Treatment Class
 *
 * Checks for:
 * - User profile fields used in SQL without sanitization
 * - Post meta/comment meta retrieved and used in queries
 * - Stored data from forms used in dynamic SQL
 * - Username/email fields used in WHERE clauses
 * - Custom fields concatenated into queries
 *
 * Second-order SQLi is harder to detect because the injection
 * happens in two stages: storage (safe) then retrieval + usage (unsafe).
 * This makes it particularly dangerous as standard input validation
 * won't catch it.
 *
 * @since 1.2033.2105
 */
class Treatment_Second_Order_SQL_Injection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $slug = 'second-order-sql-injection';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $title = 'Second-Order SQL Injection';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $description = 'Detects second-order SQL injection where stored data is used unsafely';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans code for patterns where database-retrieved values
	 * are used directly in SQL queries without proper escaping.
	 *
	 * @since  1.2033.2105
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Second_Order_SQL_Injection' );
	}

	/**
	 * Scan directory for PHP files.
	 *
	 * @since  1.2033.2105
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files to scan.
	 * @return array File paths.
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
	 * Scan file for dangerous patterns.
	 *
	 * @since  1.2033.2105
	 * @param  string $file File path.
	 * @param  array  $patterns Patterns to search for.
	 * @return array Violations found.
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
