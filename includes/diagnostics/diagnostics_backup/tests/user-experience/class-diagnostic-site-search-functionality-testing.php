<?php
/**
 * Site Search Functionality Testing Diagnostic
 *
 * Validates internal site search returns relevant results.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Search Functionality Testing Class
 *
 * Tests search functionality.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Site_Search_Functionality_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-search-functionality-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Search Functionality Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates internal site search returns relevant results';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$search_check = self::check_search_functionality();
		
		if ( $search_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $search_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-search-functionality-testing',
				'meta'         => array(
					'search_plugin_active' => $search_check['search_plugin_active'],
					'test_results'         => $search_check['test_results'],
				),
			);
		}

		return null;
	}

	/**
	 * Check search functionality.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_search_functionality() {
		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'search_plugin_active' => false,
			'test_results'         => array(),
		);

		// Check for search enhancement plugins.
		$search_plugins = array(
			'relevanssi/relevanssi.php',
			'relevanssi-premium/relevanssi.php',
			'searchwp/index.php',
			'wp-search-with-algolia/algolia.php',
			'elasticpress/elasticpress.php',
		);

		foreach ( $search_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['search_plugin_active'] = true;
				break;
			}
		}

		// Get a recent post to test search.
		$recent_posts = get_posts( array(
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( ! empty( $recent_posts ) ) {
			$test_post = $recent_posts[0];
			$test_term = '';

			// Extract first word from title for search test.
			$title_words = explode( ' ', $test_post->post_title );
			if ( ! empty( $title_words ) ) {
				$test_term = sanitize_text_field( $title_words[0] );
			}

			if ( ! empty( $test_term ) && strlen( $test_term ) > 3 ) {
				// Perform search query.
				$search_args = array(
					's'              => $test_term,
					'posts_per_page' => 10,
					'post_status'    => 'publish',
				);

				$search_query = new \WP_Query( $search_args );

				$check['test_results']['search_term'] = $test_term;
				$check['test_results']['result_count'] = $search_query->found_posts;
				$check['test_results']['expected_post_found'] = false;

				// Check if expected post is in results.
				if ( $search_query->have_posts() ) {
					while ( $search_query->have_posts() ) {
						$search_query->the_post();
						if ( get_the_ID() === $test_post->ID ) {
							$check['test_results']['expected_post_found'] = true;
							break;
						}
					}
					wp_reset_postdata();
				}

				// Flag if expected post not found.
				if ( ! $check['test_results']['expected_post_found'] ) {
					$check['has_issues'] = true;
					$check['issues'][] = sprintf(
						/* translators: %s: search term */
						__( 'Search for "%s" did not return expected content (relevance issues detected)', 'wpshadow' ),
						$test_term
					);
				}
			}
		}

		// Flag if using default WordPress search (no enhancement plugin).
		if ( ! $check['search_plugin_active'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Using default WordPress search (poor relevance, no typo tolerance)', 'wpshadow' );
		}

		return $check;
	}
}
