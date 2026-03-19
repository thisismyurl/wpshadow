<?php
/**
 * Reflected XSS Diagnostic
 *
 * Detects potential reflected XSS vulnerabilities where user input
 * from URLs or forms is echoed back without proper sanitization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reflected XSS Diagnostic Class
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
 * @since 1.6093.1200
 */
class Diagnostic_Reflected_XSS extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'reflected-xss';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Reflected XSS Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects potential reflected (non-persistent) XSS vulnerabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans theme and plugin code for patterns indicating
	 * reflected XSS vulnerabilities.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$files_scanned = 0;
		$max_files = 300;

		// Patterns indicating potential reflected XSS.
		$dangerous_patterns = array(
			// Direct $_GET output.
			'/echo\s+\$_GET\[/' => 'Direct $_GET output without escaping',
			'/print\s+\$_GET\[/' => 'Direct $_GET print without escaping',
			'/\?>\s*<[^>]*>\s*<?php\s+echo\s+\$_GET/' => 'Inline $_GET output',
			
			// Direct $_REQUEST output.
			'/echo\s+\$_REQUEST\[/' => 'Direct $_REQUEST output',
			
			// Search query output.
			'/echo\s+get_search_query\s*\(\s*\)(?!\s*\))/' => 'Unescaped search query output',
			'/echo\s+\$_GET\[["\']s["\']\]/' => 'Search parameter output',
			
			// Error message output.
			'/echo\s+["\'].*\$_GET.*["\']/' => 'Error message with $_GET interpolation',
			'/echo\s+sprintf\s*\([^)]*\$_GET/' => 'sprintf with $_GET without escaping',
			
			// URL parameter in href.
			'/href\s*=\s*["\'][^"\']*\$_GET/' => 'URL parameter in href without esc_url',
			
			// Form value attribute.
			'/value\s*=\s*["\'][^"\']*\$_(?:GET|POST|REQUEST)/' => 'Form value without esc_attr',
			
			// JavaScript variable assignment from GET.
			'/var\s+\w+\s*=\s*["\'][^"\']*\$_GET/' => 'JavaScript variable from $_GET',
		);

		// Get active theme.
		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan theme files.
		$theme_files = self::scan_directory_for_php_files( $theme_dir, 50 );
		$files_scanned += count( $theme_files );

		foreach ( $theme_files as $file ) {
			$violations = self::scan_file_for_reflected_xss( $file, $dangerous_patterns );
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
					$violations = self::scan_file_for_reflected_xss( $file, $dangerous_patterns );
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

		// Check WordPress core search functionality.
		if ( self::search_form_vulnerable() ) {
			$issues[] = array(
				'type'       => 'core',
				'violations' => array( __( 'Search form may be vulnerable to reflected XSS', 'wpshadow' ) ),
			);
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
					/* translators: 1: number of violations, 2: number of locations */
					_n(
						'Found %1$d potential reflected XSS vulnerability in %2$d location',
						'Found %1$d potential reflected XSS vulnerabilities in %2$d locations',
						$total_violations,
						'wpshadow'
					),
					$total_violations,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/reflected-xss',
				'context'      => array(
					'issues'        => $issues,
					'files_scanned' => $files_scanned,
					'why'           => __(
						'Reflected XSS allows attackers to inject malicious scripts via URL parameters or form inputs. ' .
						'When victims click malicious links, the script executes in their browser with their credentials. ' .
						'Attackers can steal session cookies, redirect to phishing sites, or modify page content. ' .
						'According to Acunetix, 53% of web applications have XSS vulnerabilities, with reflected XSS being the most common type (75%).',
						'wpshadow'
					),
					'recommendation' => __(
						'Always escape output: esc_html() for text, esc_attr() for attributes, esc_url() for URLs, esc_js() for JavaScript. ' .
						'Use get_search_query(false) instead of get_search_query() to get escaped query. ' .
						'Sanitize input with sanitize_text_field() but always escape output. Never trust user input.',
						'wpshadow'
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security (when available).
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'code-analysis',
				'xss-prevention'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Scan directory for PHP files.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
