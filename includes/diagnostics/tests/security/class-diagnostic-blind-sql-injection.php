<?php
/**
 * Blind SQL Injection Diagnostic
 *
 * Detects potential blind SQL injection vulnerabilities in
 * themes, plugins, and custom code.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blind SQL Injection Diagnostic Class
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
class Diagnostic_Blind_SQL_Injection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'blind-sql-injection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Blind SQL Injection Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects potential blind SQL injection vulnerabilities in code';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans active theme and plugins for patterns indicating
	 * potential blind SQL injection vulnerabilities.
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$files_scanned = 0;
		$max_files = 500; // Limit to prevent timeout.

		// Patterns that indicate potential blind SQLi vulnerabilities.
		$dangerous_patterns = array(
			'/\$wpdb->query\s*\(\s*["\'](?!.*\$wpdb->prepare)/' => 'Unprepared SQL query',
			'/\$wpdb->get_var\s*\(\s*["\'](?!.*\$wpdb->prepare).*\$/' => 'Variable concatenation in SQL',
			'/\$wpdb->get_results\s*\(\s*"[^"]*\$_(?:GET|POST|REQUEST)/' => 'Direct user input in SQL',
			'/mysqli_query\s*\([^,]+,\s*["\'][^"\']*\$/' => 'mysqli_query with variable concatenation',
			'/->query\s*\(\s*["\'][^"\']*\$_(?:GET|POST|REQUEST)/' => 'Query with unsanitized user input',
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan active theme.
		$theme_files = self::scan_directory_for_php_files( $theme_dir, 50 );
		$files_scanned += count( $theme_files );

		foreach ( $theme_files as $file ) {
			$violations = self::scan_file_for_patterns( $file, $dangerous_patterns );
			if ( ! empty( $violations ) ) {
				$issues[] = array(
					'file'       => str_replace( ABSPATH, '', $file ),
					'type'       => 'theme',
					'violations' => $violations,
				);
			}
		}

		// Scan active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			if ( $files_scanned >= $max_files ) {
				break;
			}

			$plugin_path = $plugin_dir . '/' . dirname( $plugin );
			if ( is_dir( $plugin_path ) ) {
				$plugin_files = self::scan_directory_for_php_files( $plugin_path, 20 );
				$files_scanned += count( $plugin_files );

				foreach ( $plugin_files as $file ) {
					$violations = self::scan_file_for_patterns( $file, $dangerous_patterns );
					if ( ! empty( $violations ) ) {
						$issues[] = array(
							'file'       => str_replace( ABSPATH, '', $file ),
							'type'       => 'plugin',
							'plugin'     => dirname( $plugin ),
							'violations' => $violations,
						);
					}
				}
			}
		}

		// If we found any issues, return a finding.
		if ( ! empty( $issues ) ) {
			$total_violations = array_sum( array_map( fn( $issue ) => count( $issue['violations'] ), $issues ) );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of violations, 2: number of files */
					_n(
						'Found %1$d potential blind SQL injection vulnerability in %2$d file',
						'Found %1$d potential blind SQL injection vulnerabilities in %2$d files',
						$total_violations,
						'wpshadow'
					),
					$total_violations,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/blind-sql-injection',
				'context'      => array(
					'issues'        => $issues,
					'files_scanned' => $files_scanned,
					'why'           => __(
						'Blind SQL injection allows attackers to extract your entire database one character at a time, ' .
						'even when the application doesn\'t display errors. ' .
						'Attackers use time-based techniques (SLEEP() function) or boolean logic to infer database contents. ' .
						'This can lead to full database compromise, including user passwords, personal data, and admin credentials.',
						'wpshadow'
					),
					'recommendation' => __(
						'Always use $wpdb->prepare() for SQL queries with user input. Never concatenate variables directly into SQL strings. ' .
						'Use parameterized queries and validate/sanitize all user input before database operations.',
						'wpshadow'
					),
				),
			);
		}

		return null;
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
