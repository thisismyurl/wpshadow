<?php
/**
 * Media Search Performance Diagnostic
 *
 * Measures media library search speed. Tests search query optimization and
 * identifies performance bottlenecks.
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
 * Media Search Performance Diagnostic Class
 *
 * Checks for search performance issues in the media library.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Search_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-search-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Search Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures media library search speed and query optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Count total media items.
		$total_media = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'"
		);

		if ( $total_media < 50 ) {
			// Not enough media to test search performance.
			return null;
		}

		// Test basic search performance.
		$start_time = microtime( true );

		$search_query = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'image',
			'posts_per_page' => 20,
		) );

		$search_time = microtime( true ) - $start_time;

		if ( $search_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Media search took %ss (slow, optimize queries)', 'wpshadow' ),
				number_format( $search_time, 2 )
			);
		} elseif ( $search_time > 0.5 && $total_media > 1000 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Media search took %ss (acceptable but could be faster)', 'wpshadow' ),
				number_format( $search_time, 2 )
			);
		}

		// Check if search uses LIKE queries (inefficient).
		$last_query = $wpdb->last_query;

		if ( strpos( $last_query, 'LIKE' ) !== false ) {
			$like_count = substr_count( $last_query, 'LIKE' );

			if ( $like_count > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of LIKE clauses */
					__( 'Search uses %d LIKE clauses (consider full-text search)', 'wpshadow' ),
					$like_count
				);
			}
		}

		// Check if search includes post meta (very slow).
		if ( strpos( $last_query, 'postmeta' ) !== false || strpos( $last_query, 'meta_' ) !== false ) {
			if ( $search_time > 0.3 ) {
				$issues[] = __( 'Search includes post meta (slow, consider separate meta search)', 'wpshadow' );
			}
		}

		// Test filename search performance.
		$start_time = microtime( true );

		$filename_search = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'attachment'
				AND pm.meta_key = '_wp_attached_file'
				AND pm.meta_value LIKE %s
				LIMIT 20",
				'%test%'
			),
			ARRAY_A
		);

		$filename_time = microtime( true ) - $start_time;

		if ( $filename_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Filename search took %ss (add index on _wp_attached_file)', 'wpshadow' ),
				number_format( $filename_time, 2 )
			);
		}

		// Check for fulltext index on post_title and post_content.
		$fulltext_indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->posts}
				WHERE Index_type = %s
				AND Column_name IN ('post_title', 'post_content')",
				'FULLTEXT'
			),
			ARRAY_A
		);

		if ( empty( $fulltext_indexes ) && $total_media > 1000 ) {
			$issues[] = __( 'No FULLTEXT index on post_title/post_content (search will be slow)', 'wpshadow' );
		}

		// Test search with MIME type filter.
		$start_time = microtime( true );

		$mime_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'photo',
			'post_mime_type' => 'image/jpeg',
			'posts_per_page' => 20,
		) );

		$mime_search_time = microtime( true ) - $start_time;

		if ( $mime_search_time > 0.8 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'MIME-filtered search took %ss (add index on post_mime_type)', 'wpshadow' ),
				number_format( $mime_search_time, 2 )
			);
		}

		// Check search relevance scoring.
		$relevance_query = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'test',
			'orderby'        => 'relevance',
			'posts_per_page' => 10,
		) );

		if ( empty( $relevance_query->posts ) && $search_query->found_posts > 0 ) {
			$issues[] = __( 'Search relevance scoring not working (check orderby=relevance)', 'wpshadow' );
		}

		// Test partial word search.
		$start_time = microtime( true );

		$partial_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'ima',
			'posts_per_page' => 20,
		) );

		$partial_time = microtime( true ) - $start_time;

		if ( $partial_time >1.0 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Partial word search took %ss (wildcard queries are slow)', 'wpshadow' ),
				number_format( $partial_time, 2 )
			);
		}

		// Check for search caching.
		$search_cache_key = 'media_search_' . md5( 'image' );
		$cached_search = wp_cache_get( $search_cache_key, 'wpshadow' );

		if ( false === $cached_search && $total_media > 500 ) {
			$issues[] = __( 'Search results not cached (enable object caching)', 'wpshadow' );
		}

		// Test alt text search (should search postmeta).
		$start_time = microtime( true );

		$alt_search = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'attachment'
				AND pm.meta_key = '_wp_attachment_image_alt'
				AND pm.meta_value LIKE %s
				LIMIT 20",
				'%test%'
			),
			ARRAY_A
		);

		$alt_time = microtime( true ) - $start_time;

		if ( $alt_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Alt text search took %ss (add index on _wp_attachment_image_alt)', 'wpshadow' ),
				number_format( $alt_time, 2 )
			);
		}

		// Check for posts_search filter modifications.
		$search_filters = $GLOBALS['wp_filter']['posts_search'] ?? null;

		if ( $search_filters && count( $search_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on posts_search (may slow searches)', 'wpshadow' ),
				count( $search_filters->callbacks )
			);
		}

		// Test search with date filter.
		$start_time = microtime( true );

		$date_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'image',
			'date_query'     => array(
				array(
					'after' => '1 year ago',
				),
			),
			'posts_per_page' => 20,
		) );

		$date_search_time = microtime( true ) - $start_time;

		if ( $date_search_time >1.0 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Date-filtered search took %ss (optimize date queries)', 'wpshadow' ),
				number_format( $date_search_time, 2 )
			);
		}

		// Check for search query complexity.
		if ( $last_query ) {
			$query_length = strlen( $last_query );

			if ( $query_length > 5000 ) {
				$issues[] = sprintf(
					/* translators: %d: query length */
					__( 'Search query is %d chars (very complex, simplify)', 'wpshadow' ),
					$query_length
				);
			}
		}

		// Test empty search (should return all).
		$start_time = microtime( true );

		$empty_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => '',
			'posts_per_page' => 20,
		) );

		$empty_time = microtime( true ) - $start_time;

		if ( $empty_time > 0.5 ) {
			$issues[] = sprintf(
				/* translators: %s: query time */
				__( 'Empty search took %ss (should be fast)', 'wpshadow' ),
				number_format( $empty_time, 2 )
			);
		}

		// Check for search stopwords.
		$stopwords = array( 'a', 'an', 'the', 'and', 'or', 'but' );
		$stopword_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => $stopwords[0],
			'posts_per_page' => 5,
		) );

		if ( $stopword_search->found_posts === 0 && $total_media > 100 ) {
			// Stopwords might be filtered out (depends on MySQL version).
			$issues[] = __( 'Stopword search returns no results (MySQL FULLTEXT limitation)', 'wpshadow' );
		}

		// Test REST API search performance.
		$rest_search_start = microtime( true );

		$rest_request = new \WP_REST_Request( 'GET', '/wp/v2/media' );
		$rest_request->set_param( 'search', 'image' );
		$rest_request->set_param( 'per_page', 20 );

		$rest_response = rest_do_request( $rest_request );

		$rest_search_time = microtime( true ) - $rest_search_start;

		if ( $rest_search_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'REST API media search took %ss (optimize for apps)', 'wpshadow' ),
				number_format( $rest_search_time, 2 )
			);
		}

		// Check for slow query logging.
		$slow_query_log = $wpdb->get_var(
			"SHOW VARIABLES LIKE 'slow_query_log'"
		);

		if ( 'ON' === $slow_query_log ) {
			// Check for recent slow searches.
			$slow_log_file = $wpdb->get_var(
				"SHOW VARIABLES LIKE 'slow_query_log_file'"
			);
		}

		// Test search with author filter.
		$start_time = microtime( true );

		$author_search = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			's'              => 'test',
			'author'         => 1,
			'posts_per_page' => 20,
		) );

		$author_search_time = microtime( true ) - $start_time;

		if ( $author_search_time > 0.8 ) {
			$issues[] = sprintf(
				/* translators: %s: search time */
				__( 'Author-filtered search took %ss (optimize combined filters)', 'wpshadow' ),
				number_format( $author_search_time, 2 )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/media-search-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
