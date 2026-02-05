<?php
/**
 * Media Query Performance Treatment
 *
 * Tests database query performance for media library operations
 * and identifies slow queries affecting attachment retrieval.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1545
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Query_Performance Class
 *
 * Ensures attachment queries are optimized and identifies
 * performance issues with media library database operations.
 *
 * @since 1.6033.1545
 */
class Treatment_Media_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests database query performance for media operations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		$issues = array();

		// Test basic attachment count query.
		$start = microtime( true );
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);
		$count_time = microtime( true ) - $start;

		if ( $count_time > 1.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Counting attachments took %s seconds; database optimization needed', 'wpshadow' ),
				number_format( $count_time, 2 )
			);
		}

		// Test attachment query with MIME type filter.
		$start = microtime( true );
		$images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type LIKE %s 
				LIMIT 20",
				'image/%'
			)
		);
		$mime_time = microtime( true ) - $start;

		if ( $mime_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Querying attachments by MIME type took %s seconds; index may be missing', 'wpshadow' ),
				number_format( $mime_time, 2 )
			);
		}

		// Test postmeta query (common for attachment metadata).
		if ( ! empty( $images ) ) {
			$attachment_ids = wp_list_pluck( $images, 'ID' );
			$start = microtime( true );
			
			$metadata = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id, meta_key, meta_value 
					FROM {$wpdb->postmeta} 
					WHERE post_id IN (" . implode( ',', array_fill( 0, count( $attachment_ids ), '%d' ) ) . ')
					AND meta_key = %s',
					array_merge( $attachment_ids, array( '_wp_attachment_metadata' ) )
				)
			);
			
			$meta_time = microtime( true ) - $start;

			if ( $meta_time > 1.5 ) {
				$issues[] = sprintf(
					/* translators: %s: query time in seconds */
					__( 'Querying attachment metadata took %s seconds; consider caching', 'wpshadow' ),
					number_format( $meta_time, 2 )
				);
			}
		}

		// Check for proper indexes on posts table.
		$indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->posts}"
		);

		$has_type_index = false;
		$has_mime_index = false;
		$has_parent_index = false;

		foreach ( $indexes as $index ) {
			if ( 'post_type' === $index->Column_name ) {
				$has_type_index = true;
			}
			if ( 'post_mime_type' === $index->Column_name ) {
				$has_mime_index = true;
			}
			if ( 'post_parent' === $index->Column_name ) {
				$has_parent_index = true;
			}
		}

		if ( ! $has_type_index ) {
			$issues[] = __( 'Missing index on post_type column; attachment queries will be slower', 'wpshadow' );
		}

		if ( ! $has_mime_index ) {
			$issues[] = __( 'Missing index on post_mime_type column; filtering by file type will be slow', 'wpshadow' );
		}

		if ( ! $has_parent_index ) {
			$issues[] = __( 'Missing index on post_parent column; finding attached media will be slow', 'wpshadow' );
		}

		// Check postmeta table indexes.
		$meta_indexes = $wpdb->get_results(
			"SHOW INDEX FROM {$wpdb->postmeta}"
		);

		$has_post_id_index = false;
		$has_meta_key_index = false;

		foreach ( $meta_indexes as $index ) {
			if ( 'post_id' === $index->Column_name ) {
				$has_post_id_index = true;
			}
			if ( 'meta_key' === $index->Column_name ) {
				$has_meta_key_index = true;
			}
		}

		if ( ! $has_post_id_index ) {
			$issues[] = __( 'Missing index on postmeta.post_id; metadata queries will be very slow', 'wpshadow' );
		}

		if ( ! $has_meta_key_index ) {
			$issues[] = __( 'Missing index on postmeta.meta_key; metadata lookups will be inefficient', 'wpshadow' );
		}

		// Test query with orderby and complex filtering.
		$start = microtime( true );
		$complex_query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_wp_attachment_metadata',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		$complex_time = microtime( true ) - $start;

		if ( $complex_time > 3.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Complex attachment query took %s seconds; optimize database or add caching', 'wpshadow' ),
				number_format( $complex_time, 2 )
			);
		}

		// Check if object caching is enabled.
		$using_object_cache = wp_using_ext_object_cache();
		
		if ( ! $using_object_cache && (int) $count > 5000 ) {
			$issues[] = __( 'Object caching is not enabled; consider enabling for better query performance with large media libraries', 'wpshadow' );
		}

		// Test search query performance.
		$start = microtime( true );
		$search_query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				's'              => 'test',
				'posts_per_page' => 10,
			)
		);
		$search_time = microtime( true ) - $start;

		if ( $search_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Media search query took %s seconds; consider using a search plugin', 'wpshadow' ),
				number_format( $search_time, 2 )
			);
		}

		// Check for unattached media queries.
		$start = microtime( true );
		$unattached = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent = 0"
		);
		$unattached_time = microtime( true ) - $start;

		if ( $unattached_time > 1.5 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Querying unattached media took %s seconds; post_parent index may be missing', 'wpshadow' ),
				number_format( $unattached_time, 2 )
			);
		}

		// Check query monitoring.
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			$issues[] = __( 'Query monitoring is disabled; enable SAVEQUERIES to debug slow media queries', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-query-performance',
			);
		}

		return null;
	}
}
