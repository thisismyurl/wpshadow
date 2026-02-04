<?php
/**
 * Stored XSS Diagnostic
 *
 * Detects potential stored (persistent) XSS vulnerabilities where
 * malicious scripts are saved to the database and executed when viewed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stored XSS Diagnostic Class
 *
 * Checks for:
 * - Unescaped output in post content rendering
 * - Comment display without proper sanitization
 * - Custom field output without escaping
 * - User profile field rendering
 * - Plugin/theme code that echoes database content unsafely
 *
 * Stored XSS is more dangerous than reflected XSS because the
 * malicious script persists in the database and affects all users
 * who view the content, not just the victim who clicked a link.
 *
 * @since 1.2033.2102
 */
class Diagnostic_Stored_XSS extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'stored-xss';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Stored XSS Vulnerability';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects potential stored (persistent) XSS vulnerabilities';

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
	 * Scans theme and plugin code for patterns that indicate
	 * potential stored XSS vulnerabilities.
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$files_scanned = 0;
		$max_files = 300;

		// Patterns indicating potential stored XSS vulnerabilities.
		$dangerous_patterns = array(
			// Post content output without escaping.
			'/echo\s+\$post->post_content(?!\s*\))/' => 'Unescaped post content output',
			'/echo\s+get_post_meta\s*\([^)]+\)(?!\s*\))/' => 'Unescaped post meta output',
			
			// Comment output without escaping.
			'/echo\s+\$comment->comment_content(?!\s*\))/' => 'Unescaped comment content',
			'/echo\s+get_comment_meta\s*\([^)]+\)(?!\s*\))/' => 'Unescaped comment meta',
			
			// User data output.
			'/echo\s+\$user->(?:display_name|user_description|user_url)(?!\s*\))/' => 'Unescaped user data output',
			'/echo\s+get_user_meta\s*\([^)]+\)(?!\s*\))/' => 'Unescaped user meta output',
			
			// Option output.
			'/echo\s+get_option\s*\([^)]+\)(?!\s*\))/' => 'Unescaped option output',
			
			// Custom field output.
			'/echo\s+\$_POST\[[^\]]+\](?!\s*\))/' => 'Direct POST data output',
			
			// the_content without filters.
			'/echo\s+apply_filters\s*\(\s*["\']the_content["\']/' => 'Manual the_content filter (risky)',
		);

		// Get active theme directory.
		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan active theme.
		$theme_files = self::scan_directory_for_php_files( $theme_dir, 50 );
		$files_scanned += count( $theme_files );

		foreach ( $theme_files as $file ) {
			$violations = self::scan_file_for_xss_patterns( $file, $dangerous_patterns );
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
					$violations = self::scan_file_for_xss_patterns( $file, $dangerous_patterns );
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

		// Check for actual malicious content in database (advanced detection).
		$malicious_found = self::scan_database_for_malicious_content();
		if ( ! empty( $malicious_found ) ) {
			$issues[] = array(
				'type'       => 'database',
				'violations' => $malicious_found,
			);
		}

		// If we found any issues, return a finding.
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
						'Found %1$d potential stored XSS vulnerability in %2$d location',
						'Found %1$d potential stored XSS vulnerabilities in %2$d locations',
						$total_violations,
						'wpshadow'
					),
					$total_violations,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/stored-xss',
				'context'      => array(
					'issues'        => $issues,
					'files_scanned' => $files_scanned,
					'why'           => __(
						'Stored XSS allows attackers to inject malicious JavaScript that executes for every user who views the content. ' .
						'Unlike reflected XSS, the attack persists in your database and affects all visitors automatically. ' .
						'Attackers can steal admin cookies, create admin accounts, modify content, redirect users to phishing sites, ' .
						'or install malware. According to OWASP, XSS is the 3rd most common web vulnerability.',
						'wpshadow'
					),
					'recommendation' => __(
						'Always escape output: use esc_html() for plain text, esc_attr() for attributes, esc_url() for URLs. ' .
						'Use wp_kses_post() when you need to allow some HTML tags. Never output user input or database content directly. ' .
						'WordPress functions like the_content(), the_title() already include proper escaping.',
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
	 * Scan a directory for PHP files.
	 *
	 * @since  1.2033.2102
	 * @param  string $dir Directory to scan.
	 * @param  int    $limit Maximum number of files.
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
	 * Scan a file for XSS patterns.
	 *
	 * @since  1.2033.2102
	 * @param  string $file File path.
	 * @param  array  $patterns Array of patterns.
	 * @return array Array of violations found.
	 */
	private static function scan_file_for_xss_patterns( $file, $patterns ) {
		$violations = array();

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return $violations;
		}

		// Skip if file uses proper escaping functions frequently.
		$escape_count = preg_match_all( '/esc_(?:html|attr|url|js|textarea)/', $content );
		$total_echoes = preg_match_all( '/echo\s+/', $content );
		
		// If >50% of echoes use escaping, probably safe (heuristic).
		if ( $total_echoes > 0 && ( $escape_count / $total_echoes ) > 0.5 ) {
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
	 * Scan database for actual malicious content.
	 *
	 * @since  1.2033.2102
	 * @return array Array of malicious content found.
	 */
	private static function scan_database_for_malicious_content() {
		global $wpdb;

		$malicious = array();

		// Check for script tags in post content.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$script_tags = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%<script%' 
			AND post_status = 'publish'"
		);

		if ( $script_tags > 0 ) {
			$malicious[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d published posts containing <script> tags', 'wpshadow' ),
				$script_tags
			);
		}

		// Check for javascript: URLs in comments.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$js_urls = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE comment_content LIKE '%javascript:%' 
			AND comment_approved = '1'"
		);

		if ( $js_urls > 0 ) {
			$malicious[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d approved comments with javascript: URLs', 'wpshadow' ),
				$js_urls
			);
		}

		// Check for onerror/onclick handlers in post content.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$event_handlers = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content REGEXP 'on(error|load|click|mouseover)=' 
			AND post_status = 'publish'"
		);

		if ( $event_handlers > 0 ) {
			$malicious[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d published posts with inline event handlers (onerror, onclick, etc)', 'wpshadow' ),
				$event_handlers
			);
		}

		return $malicious;
	}
}
