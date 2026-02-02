<?php
/**
 * DOM-Based XSS Diagnostic
 *
 * Detects DOM-based XSS vulnerabilities where JavaScript code
 * unsafely manipulates the DOM with user-controllable data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2105
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DOM-Based XSS Diagnostic Class
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
class Diagnostic_DOM_Based_XSS extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $slug = 'dom-based-xss';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $title = 'DOM-Based XSS Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2105
	 * @var   string
	 */
	protected static $description = 'Detects DOM-based XSS vulnerabilities in JavaScript code';

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
	 * Scans JavaScript files for dangerous DOM manipulation patterns.
	 *
	 * @since  1.2033.2105
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Patterns indicating DOM-based XSS.
		$dangerous_patterns = array(
			// innerHTML with location/URL data.
			'/\.innerHTML\s*=\s*.*(?:location\.|window\.location|document\.location|document\.URL)/' => 'innerHTML with URL/location data',
			
			// document.write with location.
			'/document\.write\s*\(\s*.*(?:location\.|window\.location|document\.URL)/' => 'document.write with URL data',
			
			// jQuery .html() with location.
			'/\$\([^)]+\)\.html\s*\(\s*.*(?:location\.|window\.location|document\.URL)/' => 'jQuery .html() with URL data',
			
			// jQuery .append() with location.
			'/\$\([^)]+\)\.append\s*\(\s*.*(?:location\.|window\.location|document\.URL)/' => 'jQuery .append() with URL data',
			
			// eval with location/URL.
			'/eval\s*\(\s*.*(?:location\.|window\.location|document\.URL)/' => 'eval() with URL data',
			
			// Setting event handlers dynamically.
			'/\.setAttribute\s*\(\s*["\']on(?:click|load|error|mouseover)["\']/' => 'Dynamic event handler via setAttribute',
			
			// innerHTML with hash/search.
			'/\.innerHTML\s*=\s*.*(?:location\.hash|location\.search)/' => 'innerHTML with location.hash or location.search',
		);

		// Scan theme JavaScript.
		$theme_js_files = self::find_javascript_files( get_stylesheet_directory() );
		foreach ( $theme_js_files as $file ) {
			$violations = self::scan_js_file( $file, $dangerous_patterns );
			if ( ! empty( $violations ) ) {
				$issues[] = array(
					'file'       => str_replace( ABSPATH, '', $file ),
					'type'       => 'theme',
					'violations' => $violations,
				);
			}
		}

		// Scan plugin JavaScript.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_dir = WP_PLUGIN_DIR;
		
		foreach ( array_slice( $active_plugins, 0, 10 ) as $plugin ) {
			$plugin_path = $plugin_dir . '/' . dirname( $plugin );
			if ( is_dir( $plugin_path ) ) {
				$plugin_js = self::find_javascript_files( $plugin_path, 10 );
				foreach ( $plugin_js as $file ) {
					$violations = self::scan_js_file( $file, $dangerous_patterns );
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

		// Check for inline JavaScript in theme.
		$inline_issues = self::scan_php_for_inline_js();
		if ( ! empty( $inline_issues ) ) {
			$issues = array_merge( $issues, $inline_issues );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$total_violations = array_sum(
				array_map(
					fn( $issue ) => count( $issue['violations'] ),
					$issues
				)
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of violations, 2: number of files */
					_n(
						'Found %1$d potential DOM-based XSS vulnerability in %2$d file',
						'Found %1$d potential DOM-based XSS vulnerabilities in %2$d files',
						$total_violations,
						'wpshadow'
					),
					$total_violations,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dom-based-xss',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'DOM-based XSS is particularly dangerous because it executes entirely in the browser, ' .
						'never touching the server. Traditional server-side security measures (WAFs, input validation) ' .
						'cannot detect or prevent it. Attackers can inject malicious scripts via URL fragments (#payload), ' .
						'query parameters, or postMessage events. The vulnerability lies in how JavaScript processes ' .
						'user-controllable data and writes it to the DOM. According to OWASP, DOM XSS accounts for ' .
						'approximately 25% of all XSS vulnerabilities.',
						'wpshadow'
					),
					'recommendation' => __(
						'Use textContent instead of innerHTML for user data. For jQuery, use .text() instead of .html(). ' .
						'Validate and sanitize URL parameters before using them. Never use eval() with user input. ' .
						'Use DOMPurify library for HTML sanitization. Avoid document.write() entirely. ' .
						'Set Content Security Policy (CSP) headers to restrict inline JavaScript.',
						'wpshadow'
					),
				),
			);
		}

		return null;
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
