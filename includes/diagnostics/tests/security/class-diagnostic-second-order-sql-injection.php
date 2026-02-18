<?php
/**
 * Second-Order SQL Injection Diagnostic
 *
 * Detects second-order SQL injection vulnerabilities where malicious
 * input is stored and later used in unsafe SQL queries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2105
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Second-Order SQL Injection Diagnostic Class
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
class Diagnostic_Second_Order_SQL_Injection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $slug = 'second-order-sql-injection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $title = 'Second-Order SQL Injection';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $description = 'Detects second-order SQL injection where stored data is used unsafely';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans code for patterns where database-retrieved values
	 * are used directly in SQL queries without proper escaping.
	 *
	 * @since  1.2033.2105
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$files_scanned = 0;
		$max_files = 300;

		// Patterns indicating second-order SQLi vulnerabilities.
		$dangerous_patterns = array(
			// User meta retrieved and used in query.
			'/get_user_meta\([^)]+\)[^;]+\$wpdb->(?:query|get_var|get_results)\s*\([^)]*\$/' => 'User meta used directly in SQL query',
			
			// Post meta used in query.
			'/get_post_meta\([^)]+\)[^;]+\$wpdb->(?:query|get_var|get_results)\s*\([^)]*\$/' => 'Post meta used directly in SQL query',
			
			// Comment meta used in query.
			'/get_comment_meta\([^)]+\)[^;]+\$wpdb->(?:query|get_var|get_results)\s*\([^)]*\$/' => 'Comment meta used directly in SQL query',
			
			// User object properties in SQL.
			'/\$user->(?:user_login|user_email|display_name)[^;]+\$wpdb->query\s*\([^)]*\$user->/' => 'User property used directly in SQL',
			
			// Option value in SQL.
			'/get_option\([^)]+\)[^;]+\$wpdb->query\s*\([^)]*\$/' => 'Option value concatenated into SQL',
			
			// Transient in SQL.
			'/get_transient\([^)]+\)[^;]+\$wpdb->query\s*\([^)]*\$/' => 'Transient value used in SQL query',
		);

		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan theme files.
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
				$plugin_files = self::scan_directory_for_php_files( $plugin_path, 15 );
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

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$total_violations = array_sum(
				array_map(
					fn( $issue ) => count( $issue['violations'] ),
					$issues
				)
			);

			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of violations, 2: number of files */
					_n(
						'Found %1$d potential second-order SQL injection vulnerability in %2$d file',
						'Found %1$d potential second-order SQL injection vulnerabilities in %2$d files',
						$total_violations,
						'wpshadow'
					),
					$total_violations,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/second-order-sql-injection',
				'context'      => array(
					'issues'        => $issues,
					'files_scanned' => $files_scanned,
					'why'           => __(
						'Second-order SQL injection is particularly dangerous because it bypasses standard input validation. ' .
						'Malicious data is stored safely (e.g., in user profile, comment meta), but later retrieved and used ' .
						'in an unsafe SQL query without proper escaping. Attackers can inject SQL through profile updates, ' .
						'comment submissions, or form data, then trigger execution when that data is used in queries. ' .
						'This can lead to complete database compromise, privilege escalation, and data exfiltration.',
						'wpshadow'
					),
					'recommendation' => __(
						'Always use $wpdb->prepare() even for database-retrieved values. Never trust ANY data, even from your own database. ' .
						'Escape all values before concatenation: $wpdb->esc_like() for LIKE clauses, esc_sql() for identifiers. ' .
						'Validate data type and format before using in queries. Consider using ORM or query builders that enforce parameterization.',
						'wpshadow'
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security (when available).
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'code-analysis',
				'second-order-sql-injection-fix'
			);

			return $finding;
		}

		return null;
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
