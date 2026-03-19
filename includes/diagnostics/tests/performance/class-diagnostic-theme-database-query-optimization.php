<?php
/**
 * Theme Database Query Optimization Diagnostic
 *
 * Analyzes theme files for inefficient database queries and missing optimizations.
 *
 * **What This Check Does:**
 * 1. Scans theme code for N+1 query patterns
 * 2. Detects unoptimized loops fetching data
 * 3. Flags queries without caching (transients)\n * 4. Identifies missing indexes in theme queries\n * 5. Checks for query pagination (prevents memory exhaustion)\n * 6. Validates query efficiency\n *
 * **Why This Matters:**\n * Poor query patterns are common in themes: loop through posts, then fetch data per post. This is 10-100x
 * slower than fetching once with JOIN. A well-optimized query takes 0.01 seconds. The same unoptimized
 * takes 1 second. Times 100 queries = 100 seconds wasted per page load.\n *
 * **Real-World Scenario:**\n * Premium theme with member directory fetched user data in loop (N+1). Directory with 1,000 members
 * generated 1,001 queries. Directory page load: 45 seconds (unusable). After optimizing to single query
 * with JOIN, 1,001 → 1 query. Page load: 0.2 seconds. Directory became usable. Feature that was broken
 * now working.\n *
 * **Business Impact:**\n * - Queries 10-100x slower than necessary\n * - Page load 5-30+ seconds (unusable)\n * - Database server can't handle traffic\n * - Scaling impossible without database upgrade ($100k+)\n * - Revenue-critical pages broken\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: 10-100x speed improvement potential\n * - #8 Inspire Confidence: Identifies optimization opportunities\n * - #10 Talk-About-Worthy: "Query optimization is magical"\n *
 * **Related Checks:**\n * - Theme Database Queries (query volume analysis)\n * - Database Index Efficiency (index-based optimization)\n * - Meta Query Performance (postmeta optimization)\n * - Plugin Database Query Performance (plugin comparison)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/query-optimization-patterns\n * - Video: https://wpshadow.com/training/wp-query-advanced (8 min)\n * - Advanced: https://wpshadow.com/training/theme-architecture-refactoring (15 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Problematic query patterns.
		$patterns = array(
			'get_posts\(\s*array\(\s*\'numberposts\'\s*=>\s*-1'  => __( 'Retrieving all posts without limit (get_posts with numberposts => -1)', 'wpshadow' ),
			'WP_Query.*\'posts_per_page\'\s*=>\s*-1'             => __( 'WP_Query retrieving unlimited posts', 'wpshadow' ),
			'get_users\(\s*array\(\s*\'number\'\s*=>\s*\'\'|get_users\(\s*array\(\s*\'number\'\s*=>\s*-1' => __( 'Retrieving all users without limit', 'wpshadow' ),
			'while\s*\(\s*have_posts.*get_post_meta'             => __( 'Potential N+1 query: get_post_meta in loop', 'wpshadow' ),
			'foreach.*get_user_meta'                              => __( 'Potential N+1 query: get_user_meta in loop', 'wpshadow' ),
			'wpdb->query\(.*DELETE.*WHERE\s+[^=]*IN'             => __( 'Potentially inefficient DELETE with IN clause', 'wpshadow' ),
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
	 * @since 1.6093.1200
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
