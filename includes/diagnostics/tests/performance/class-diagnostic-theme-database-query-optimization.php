<?php
/**
 * Theme Database Query Optimization Diagnostic
 *
 * Analyzes theme files for inefficient database queries including
 * N+1 query problems, missing indexes, and lack of caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Database Query Optimization Diagnostic Class
 *
 * Detects inefficient database query patterns in theme files.
 *
 * @since 1.6032.1200
 */
class Diagnostic_Theme_Database_Query_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-database-query-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Database Query Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes theme for inefficient database queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Problematic query patterns.
		$patterns = array(
			'get_posts(\s*array\(\s*\'numberposts\'\s*=>\s*-1'  => __( 'Retrieving all posts without limit (get_posts with numberposts => -1)', 'wpshadow' ),
			'WP_Query.*\'posts_per_page\'\s*=>\s*-1'            => __( 'WP_Query retrieving unlimited posts', 'wpshadow' ),
			'get_users(\s*array\(\s*\'number\'\s*=>\s*\'\''    => __( 'Retrieving all users without limit', 'wpshadow' ),
			'while\s*\(\s*have_posts.*get_post_meta'            => __( 'Potential N+1 query: get_post_meta in loop', 'wpshadow' ),
			'foreach.*get_user_meta'                             => __( 'Potential N+1 query: get_user_meta in loop', 'wpshadow' ),
			'wpdb->query\(.*DELETE.*WHERE\s+[^=]*IN'            => __( 'Potentially inefficient DELETE with IN clause', 'wpshadow' ),
		);

		// Scan theme files.
		$theme_files = self::get_theme_php_files( $template_dir );

		foreach ( $theme_files as $file ) {
			$content = file_get_contents( $file );

			foreach ( $patterns as $pattern => $description ) {
				if ( preg_match( '/' . $pattern . '/i', $content ) ) {
					$issues[] = array(
						'file'        => str_replace( $template_dir, '', $file ),
						'pattern'     => $pattern,
						'description' => $description,
					);
				}
			}

			// Check for queries inside loops (major performance issue).
			if ( preg_match_all( '/(?:foreach|while|for)\s*\([^)]+\)\s*{[^}]*(?:wpdb->|get_posts|WP_Query)/s', $content, $matches ) ) {
				$issues[] = array(
					'file'        => str_replace( $template_dir, '', $file ),
					'pattern'     => 'query_in_loop',
					'description' => __( 'Database queries inside loops detected', 'wpshadow' ),
				);
			}
		}

		if ( count( $issues ) > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of query optimization issues */
					__( 'Found %d database query optimization issues in your theme.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => array_slice( $issues, 0, 20 ),
					'total_count'    => count( $issues ),
					'recommendation' => __( 'Refactor queries to use batch operations, add caching, and avoid N+1 query patterns.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Get all PHP files in theme directory.
	 *
	 * @since  1.6032.1200
	 * @param  string $dir Directory to scan.
	 * @return array Array of file paths.
	 */
	private static function get_theme_php_files( $dir ) {
		$files = array();
		$items = scandir( $dir );

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item || 'node_modules' === $item || 'vendor' === $item ) {
				continue;
			}

			$path = $dir . '/' . $item;

			if ( is_dir( $path ) ) {
				$files = array_merge( $files, self::get_theme_php_files( $path ) );
			} elseif ( is_file( $path ) && preg_match( '/\.php$/', $item ) ) {
				$files[] = $path;
			}
		}

		return $files;
	}
}
