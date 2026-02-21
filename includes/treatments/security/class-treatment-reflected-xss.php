<?php
/**
 * Reflected XSS Treatment
 *
 * Detects potential reflected XSS vulnerabilities where user input
 * from URLs or forms is echoed back without proper sanitization.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2103
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reflected XSS Treatment Class
 *
 * Checks for:
 * - $_GET parameters echoed without escaping
 * - Search functionality output without sanitization
 * - Error message output containing user input
 * - URL parameters reflected in page content
 * - Form field values output unsafely
 *
 * Reflected XSS accounts for approximately 75% of all XSS attacks.
 * According to Acunetix, XSS vulnerabilities are found in 53% of
 * web applications tested.
 *
 * @since 1.2033.2103
 */
class Treatment_Reflected_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2103
	 * @var   string
	 */
	protected static $slug = 'reflected-xss';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2103
	 * @var   string
	 */
	protected static $title = 'Reflected XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2103
	 * @var   string
	 */
	protected static $description = 'Detects potential reflected (non-persistent) XSS vulnerabilities';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2103
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans theme and plugin code for patterns indicating
	 * reflected XSS vulnerabilities.
	 *
	 * @since  1.2033.2103
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Reflected_XSS' );
	}

	/**
	 * Scan directory for PHP files.
	 *
	 * @since  1.2033.2103
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
	 * Scan file for reflected XSS patterns.
	 *
	 * @since  1.2033.2103
	 * @param  string $file File path.
	 * @param  array  $patterns Dangerous patterns.
	 * @return array Violations found.
	 */
	private static function scan_file_for_reflected_xss( $file, $patterns ) {
		$violations = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $violations;
		}

		// Check for proper escaping usage.
		$escape_functions = preg_match_all( '/esc_(?:html|attr|url|js)/', $content );
		$get_usage = preg_match_all( '/\$_(?:GET|REQUEST)\[/', $content );

		// If file uses $_GET but has proper escaping, likely safe (heuristic).
		if ( $get_usage > 0 && $escape_functions >= $get_usage ) {
			return $violations;
		}

		foreach ( $patterns as $pattern => $description ) {
			if ( preg_match( $pattern, $content ) ) {
				$violations[] = $description;
			}
		}

		return $violations;
	}

	/**
	 * Check if WordPress search form is vulnerable.
	 *
	 * @since  1.2033.2103
	 * @return bool True if vulnerable.
	 */
	private static function search_form_vulnerable() {
		// Check if searchform.php exists in theme.
		$searchform = locate_template( 'searchform.php' );
		
		if ( ! $searchform ) {
			return false; // Using WordPress default (which is safe).
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $searchform );
		if ( false === $content ) {
			return false;
		}

		// Look for unescaped search query output.
		if ( preg_match( '/value\s*=\s*["\'][^"\']*\$_GET\[["\']s["\']\]/', $content ) ) {
			return true;
		}

		// Check for get_search_query() without escaping.
		if ( preg_match( '/value\s*=\s*["\'][^"\']*get_search_query\s*\(\s*\)[^"\']*["\']/', $content ) ) {
			return true;
		}

		return false;
	}
}
