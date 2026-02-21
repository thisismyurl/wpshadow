<?php
/**
 * Stored XSS Treatment
 *
 * Detects potential stored (persistent) XSS vulnerabilities where
 * malicious scripts are saved to the database and executed when viewed.
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
 * Stored XSS Treatment Class
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
class Treatment_Stored_XSS extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'stored-xss';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Stored XSS Vulnerability';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects potential stored (persistent) XSS vulnerabilities';

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
	 * Scans theme and plugin code for patterns that indicate
	 * potential stored XSS vulnerabilities.
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Stored_XSS' );
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
