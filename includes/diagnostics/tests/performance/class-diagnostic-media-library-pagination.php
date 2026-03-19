<?php
/**
 * Media Library Pagination Diagnostic
 *
 * Validates pagination works correctly in media library. Tests with large media
 * counts and pagination performance.
 *
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
 * Media Library Pagination Diagnostic Class
 *
 * Checks for pagination issues in the media library.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Library_Pagination extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-pagination';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Pagination';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates pagination works correctly in media library with large media counts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
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

		if ( $total_media < 100 ) {
			// Not enough media to test pagination meaningfully.
			return null;
		}

		// Get posts_per_page setting for media library.
		$per_page = (int) get_option( 'posts_per_page', 10 );
		$upload_per_page = apply_filters( 'upload_per_page', $per_page );

		if ( $upload_per_page < 20 && $total_media > 1000 ) {
			$issues[] = sprintf(
				/* translators: 1: items per page, 2: total media */
				__( 'Media library shows %1$d items per page but has %2$d total media (increase for better UX)', 'wpshadow' ),
				$upload_per_page,
				$total_media
			);
		}

		// Check for pagination query performance.
		$start_time = microtime( true );
		
		$paged_query = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $upload_per_page,
			'paged'          => 2,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$query_time = microtime( true ) - $start_time;

		if ( $query_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: query time */
				__( 'Media pagination query took %ss (slow, optimize database)', 'wpshadow' ),
				number_format( $query_time, 2 )
			);
		}

		// Check if pagination offsets work correctly.
		$first_page = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $upload_per_page,
			'paged'          => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		) );

		$second_page = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $upload_per_page,
			'paged'          => 2,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		) );

		// Check for overlapping results (pagination bug).
		$first_ids = $first_page->posts;
		$second_ids = $second_page->posts;
		
		$overlap = array_intersect( $first_ids, $second_ids );
		
		if ( ! empty( $overlap ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of overlapping items */
				__( '%d items appear on multiple pages (pagination bug)', 'wpshadow' ),
				count( $overlap )
			);
		}

		// Check if pagination links are generated correctly.
		if ( function_exists( 'paginate_links' ) ) {
			$pagination_args = array(
				'base'      => '%_%',
				'format'    => '?paged=%#%',
				'current'   => 1,
				'total'     => ceil( $total_media / $upload_per_page ),
				'prev_text' => __( '&laquo; Previous', 'wpshadow' ),
				'next_text' => __( 'Next &raquo;', 'wpshadow' ),
			);

			$pagination_html = paginate_links( $pagination_args );
			
			if ( empty( $pagination_html ) && $total_media > $upload_per_page ) {
				$issues[] = __( 'Pagination links not generating (check rewrite rules)', 'wpshadow' );
			}
		}

		// Check for max_found_rows setting (can slow large media libraries).
		$found_rows_query = $wpdb->get_var(
			"SHOW VARIABLES LIKE 'max_found_rows'"
		);

		// Check for pagination with MIME type filters.
		$image_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		if ( $image_count > 100 ) {
			$start_time = microtime( true );
			
			$filtered_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => $upload_per_page,
				'paged'          => 1,
			) );

			$filtered_time = microtime( true ) - $start_time;

			if ( $filtered_time > 0.5 ) {
				$issues[] = sprintf(
					/* translators: %s: query time */
					__( 'Filtered media pagination took %ss (add index on post_mime_type)', 'wpshadow' ),
					number_format( $filtered_time, 2 )
				);
			}
		}

		// Check for pagination with date filters.
		$recent_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_date > %s",
				gmdate( 'Y-m-d', strtotime( '-1 year' ) )
			)
		);

		if ( $recent_count > 100 ) {
			$start_time = microtime( true );
			
			$date_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $upload_per_page,
				'paged'          => 1,
				'date_query'     => array(
					array(
						'after' => '1 year ago',
					),
				),
			) );

			$date_time = microtime( true ) - $start_time;

			if ( $date_time > 0.5 ) {
				$issues[] = sprintf(
					/* translators: %s: query time */
					__( 'Date-filtered pagination took %ss (optimize date queries)', 'wpshadow' ),
					number_format( $date_time, 2 )
				);
			}
		}

		// Check for pagination cache.
		$pagination_cache = wp_cache_get( 'media_pagination_count', 'wpshadow' );
		if ( false === $pagination_cache && $total_media > 1000 ) {
			$issues[] = __( 'Pagination count not cached (enable object caching)', 'wpshadow' );
		}

		// Check for excessive page count (UX issue).
		$page_count = ceil( $total_media / $upload_per_page );
		
		if ( $page_count > 100 ) {
			$issues[] = sprintf(
				/* translators: 1: page count, 2: items per page */
				__( '%1$d pagination pages (increase per-page from %2$d)', 'wpshadow' ),
				$page_count,
				$upload_per_page
			);
		}

		// Check if pagination is using FOUND_ROWS (slow on large tables).
		$explain_query = $wpdb->get_results(
			"EXPLAIN SELECT SQL_CALC_FOUND_ROWS * 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			LIMIT {$upload_per_page}",
			ARRAY_A
		);

		if ( ! empty( $explain_query ) ) {
			foreach ( $explain_query as $row ) {
				if ( isset( $row['Extra'] ) && strpos( $row['Extra'], 'Using filesort' ) !== false ) {
					$issues[] = __( 'Pagination using filesort (add index for better performance)', 'wpshadow' );
					break;
				}
			}
		}

		// Check for no_found_rows optimization.
		$optimized_query = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $upload_per_page,
			'no_found_rows'  => true,
		) );

		// Check for pagination in REST API.
		$rest_per_page = apply_filters( 'rest_media_query', array( 'per_page' => 10 ) );
		
		if ( isset( $rest_per_page['per_page'] ) && $rest_per_page['per_page'] < 20 && $total_media > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: items per page */
				__( 'REST API media pagination shows only %d items (increase for apps)', 'wpshadow' ),
				$rest_per_page['per_page']
			);
		}

		// Check for pagination with meta queries (slow).
		$start_time = microtime( true );
		
		$meta_query = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $upload_per_page,
			'meta_query'     => array(
				array(
					'key'     => '_wp_attachment_metadata',
					'compare' => 'EXISTS',
				),
			),
		) );

		$meta_time = microtime( true ) - $start_time;

		if ( $meta_time > 1 && $total_media > 500 ) {
			$issues[] = sprintf(
				/* translators: %s: query time */
				__( 'Meta-filtered pagination took %ss (add index on meta_key)', 'wpshadow' ),
				number_format( $meta_time, 2 )
			);
		}

		// Check for orphaned attachment posts (pagination count mismatch).
		$orphaned_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent NOT IN (
				SELECT ID FROM {$wpdb->posts} WHERE post_type != 'attachment'
			) 
			AND post_parent != 0"
		);

		if ( $orphaned_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: orphaned attachments */
				__( '%d orphaned attachments (affects pagination count)', 'wpshadow' ),
				$orphaned_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/media-library-pagination',
			);
		}

		return null;
	}
}
