<?php
/**
 * Search Results Template Diagnostic
 *
 * Validates that the search results template is properly configured
 * with appropriate layout, result display, and pagination.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Results Template Diagnostic Class
 *
 * Checks search template implementation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_Results_Template extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-results-template';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Results Template';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates search results template configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Check for search.php template.
		$search_file = $template_dir . '/search.php';
		if ( ! file_exists( $search_file ) ) {
			$issues[] = __( 'Missing search.php template (uses archive.php or index.php)', 'wpshadow' );
		} else {
			$content = file_get_contents( $search_file );

			// Check for search query display.
			if ( false === stripos( $content, 'get_search_query' ) && false === stripos( $content, 'the_search_query' ) ) {
				$issues[] = __( 'Search template does not display search query', 'wpshadow' );
			}

			// Check for "no results" handling.
			if ( false === stripos( $content, 'have_posts' ) || false === stripos( $content, 'else' ) ) {
				$issues[] = __( 'Search template lacks proper "no results" handling', 'wpshadow' );
			}

			// Check for search form in no results.
			$has_search_form_in_empty = preg_match( '/else.*?get_search_form/is', $content ) ||
									   preg_match( '/have_posts.*?!.*?get_search_form/is', $content );

			if ( ! $has_search_form_in_empty ) {
				$issues[] = __( 'No search form in "no results" section (users cannot retry)', 'wpshadow' );
			}

			// Check for pagination.
			$pagination_functions = array( 'the_posts_pagination', 'paginate_links', 'posts_nav_link' );
			$has_pagination       = false;

			foreach ( $pagination_functions as $func ) {
				if ( false !== stripos( $content, $func ) ) {
					$has_pagination = true;
					break;
				}
			}

			if ( ! $has_pagination ) {
				$issues[] = __( 'Search template lacks pagination (only shows first page)', 'wpshadow' );
			}
		}

		// Check WordPress search settings.
		$search_engines_blocked = (int) get_option( 'blog_public', 1 );
		if ( 0 === $search_engines_blocked ) {
			$issues[] = __( 'Search engines are blocked (affects site search functionality)', 'wpshadow' );
		}

		// Check if search is disabled via filter.
		$search_enabled = apply_filters( 'pre_get_posts', new \WP_Query() );
		// This is a simple check; more thorough testing would require actual search.

		// Check for search enhancement plugins.
		$search_plugins = array(
			'relevanssi/relevanssi.php',
			'search-everything/search-everything.php',
			'searchwp/index.php',
		);

		$has_search_enhancement = false;
		foreach ( $search_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_search_enhancement = true;
				break;
			}
		}

		// Check recent search performance.
		global $wpdb;
		$slow_searches = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'post'
			AND post_status = 'publish'"
		);

		if ( $slow_searches > 10000 && ! $has_search_enhancement ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Site has %d posts but no search enhancement plugin (searches may be slow)', 'wpshadow' ),
				$slow_searches
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of search template issues */
					__( 'Found %d search results template issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'post_count'     => $slow_searches,
					'recommendation' => __( 'Ensure search.php displays query, handles no results, and includes pagination.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
