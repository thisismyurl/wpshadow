<?php
/**
 * DOM-Based XSS Treatment
 *
 * Detects DOM-based XSS vulnerabilities where JavaScript code
 * unsafely manipulates the DOM with user-controllable data.
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
 * DOM-Based XSS Treatment Class
 *
 * Checks for:
 * - innerHTML usage with user-controllable data
 * - Unsafe jQuery methods (.html(), .append() with unsafe data)
 * - document.write() with URL parameters
 * - eval() with user input
 * - Event handler attributes set dynamically
 * - location.href manipulation
 *
 * DOM XSS differs from traditional XSS because the vulnerability
 * exists entirely in client-side code. The server never sees the
 * malicious payload, making it harder to detect and prevent.
 *
 * @since 1.2033.2105
 */
class Treatment_DOM_Based_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $slug = 'dom-based-xss';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $title = 'DOM-Based XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $description = 'Detects DOM-based XSS vulnerabilities in JavaScript code';

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
	 * Scans JavaScript files for dangerous DOM manipulation patterns.
	 *
	 * @since  1.2033.2105
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_DOM_Based_XSS' );
	}

	/**
	 * Find JavaScript files in directory.
	 *
	 * @since  1.2033.2105
	 * @param  string $dir Directory path.
	 * @param  int    $limit Maximum files to find.
	 * @return array File paths.
	 */
	private static function find_javascript_files( $dir, $limit = 50 ) {
		$files = array();
		
		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$js_dirs = array(
			$dir . '/js',
			$dir . '/assets/js',
			$dir . '/dist',
			$dir . '/build',
		);

		foreach ( $js_dirs as $js_dir ) {
			if ( ! is_dir( $js_dir ) ) {
				continue;
			}

			$found = glob( $js_dir . '/*.js' );
			if ( $found ) {
				$files = array_merge( $files, array_slice( $found, 0, $limit - count( $files ) ) );
			}

			if ( count( $files ) >= $limit ) {
				break;
			}
		}

		return $files;
	}

	/**
	 * Scan JavaScript file for dangerous patterns.
	 *
	 * @since  1.2033.2105
	 * @param  string $file File path.
	 * @param  array  $patterns Patterns to search for.
	 * @return array Violations found.
	 */
	private static function scan_js_file( $file, $patterns ) {
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

	/**
	 * Scan PHP files for inline JavaScript with DOM XSS.
	 *
	 * @since  1.2033.2105
	 * @return array Issues found.
	 */
	private static function scan_php_for_inline_js() {
		$issues = array();
		$theme_dir = get_stylesheet_directory();

		// Look for inline <script> tags in PHP templates.
		$php_files = glob( $theme_dir . '/*.php' );
		if ( ! $php_files ) {
			return $issues;
		}

		$inline_pattern = '/<script[^>]*>.*(?:innerHTML|document\.write|location\.hash|location\.search).*<\/script>/s';

		foreach ( array_slice( $php_files, 0, 20 ) as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content = file_get_contents( $file );
			if ( preg_match( $inline_pattern, $content ) ) {
				$issues[] = array(
					'file'       => str_replace( ABSPATH, '', $file ),
					'type'       => 'theme',
					'violations' => array( 'Inline JavaScript with potential DOM XSS' ),
				);
			}
		}

		return $issues;
	}
}
