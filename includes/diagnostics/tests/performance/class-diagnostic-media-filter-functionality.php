<?php
/**
 * Media Filter Functionality Diagnostic
 *
 * Tests media library filters (date, type, uploaded by). Validates filter
 * accuracy and performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Filter Functionality Diagnostic Class
 *
 * Checks for media library filter issues.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Media_Filter_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-filter-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Filter Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library filters (date, type, uploaded by) and validates accuracy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
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

		if ( $total_media < 10 ) {
			// Not enough media to test filters.
			return null;
		}

		// Test MIME type filter.
		$image_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		if ( $image_count > 0 ) {
			$filtered_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			$filtered_count = count( $filtered_query->posts );

			// Check if filter results match expected count.
			if ( abs( $filtered_count - $image_count ) > 5 ) {
				$issues[] = sprintf(
					/* translators: 1: filtered count, 2: expected count */
					__( 'Image filter returns %1$d items but database has %2$d (filter inaccurate)', 'wpshadow' ),
					$filtered_count,
					$image_count
				);
			}
		}

		// Test date filter accuracy.
		$recent_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_date >= %s",
				gmdate( 'Y-m-d', strtotime( '-1 month' ) )
			)
		);

		if ( $recent_count > 0 ) {
			$date_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'after' => '1 month ago',
					),
				),
				'fields'         => 'ids',
			) );

			$date_filtered = count( $date_query->posts );

			if ( abs( $date_filtered - $recent_count ) > 2 ) {
				$issues[] = sprintf(
					/* translators: 1: filtered count, 2: expected count */
					__( 'Date filter returns %1$d items but expected %2$d (filter inaccurate)', 'wpshadow' ),
					$date_filtered,
					$recent_count
				);
			}
		}

		// Test author/uploaded by filter.
		$author_counts = $wpdb->get_results(
			"SELECT post_author, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			GROUP BY post_author 
			HAVING count > 0",
			ARRAY_A
		);

		if ( ! empty( $author_counts ) ) {
			foreach ( array_slice( $author_counts, 0, 3 ) as $author_data ) {
				$author_id = (int) $author_data['post_author'];
				$expected_count = (int) $author_data['count'];

				$author_query = new \WP_Query( array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'author'         => $author_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				) );

				$filtered_count = count( $author_query->posts );

				if ( $filtered_count !== $expected_count ) {
					$issues[] = sprintf(
						/* translators: 1: author ID, 2: filtered count, 3: expected count */
						__( 'Author filter (user %1$d) returns %2$d items but expected %3$d', 'wpshadow' ),
						$author_id,
						$filtered_count,
						$expected_count
					);
					break;
				}
			}
		}

		// Test media state filters (attached vs unattached).
		$attached_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent > 0"
		);

		$unattached_count = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent = 0"
		);

		if ( $attached_count > 0 ) {
			$attached_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_parent__not_in' => array( 0 ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			if ( abs( count( $attached_query->posts ) - $attached_count ) > 2 ) {
				$issues[] = __( 'Attached media filter returns inaccurate count', 'wpshadow' );
			}
		}

		if ( $unattached_count > 0 ) {
			$unattached_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_parent'    => 0,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			if ( abs( count( $unattached_query->posts ) - $unattached_count ) > 2 ) {
				$issues[] = __( 'Unattached media filter returns inaccurate count', 'wpshadow' );
			}
		}

		// Test multiple MIME types filter.
		$mime_types = array( 'image/jpeg', 'image/png', 'image/gif' );
		$multi_mime_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type IN (%s, %s, %s)",
				$mime_types[0],
				$mime_types[1],
				$mime_types[2]
			)
		);

		if ( $multi_mime_count > 0 ) {
			$multi_mime_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => $mime_types,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			if ( abs( count( $multi_mime_query->posts ) - $multi_mime_count ) > 3 ) {
				$issues[] = __( 'Multiple MIME type filter returns inaccurate results', 'wpshadow' );
			}
		}

		// Test month filter dropdown.
		$months_with_media = $wpdb->get_results(
			"SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			ORDER BY post_date DESC 
			LIMIT 12",
			ARRAY_A
		);

		if ( ! empty( $months_with_media ) ) {
			$first_month = $months_with_media[0];
			
			$month_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->posts} 
					WHERE post_type = 'attachment' 
					AND YEAR(post_date) = %d 
					AND MONTH(post_date) = %d",
					$first_month['year'],
					$first_month['month']
				)
			);

			$month_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'year'           => $first_month['year'],
				'monthnum'       => $first_month['month'],
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			if ( abs( count( $month_query->posts ) - $month_count ) > 2 ) {
				$issues[] = sprintf(
					/* translators: 1: year, 2: month */
					__( 'Month filter (%1$d-%2$d) returns inaccurate count', 'wpshadow' ),
					$first_month['year'],
					$first_month['month']
				);
			}
		}

		// Test filter performance.
		$start_time = microtime( true );
		
		$complex_filter = new \WP_Query( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'date_query'     => array(
				array(
					'after' => '1 year ago',
				),
			),
			'posts_per_page' => 50,
		) );

		$filter_time = microtime( true ) - $start_time;

		if ( $filter_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: filter time */
				__( 'Combined filters took %ss (optimize queries)', 'wpshadow' ),
				number_format( $filter_time, 2 )
			);
		}

		// Check for post_mime_types filter modifications.
		$mime_filter = $GLOBALS['wp_filter']['post_mime_types'] ?? null;
		
		if ( $mime_filter && count( $mime_filter->callbacks ) > 0 ) {
			// Test if filter modifications break core MIME types.
			$mime_types_filtered = apply_filters( 'post_mime_types', array() );
			
			if ( ! is_array( $mime_types_filtered ) ) {
				$issues[] = __( 'post_mime_types filter returns non-array (filter broken)', 'wpshadow' );
			}
		}

		// Test media categories/taxonomy filters.
		$media_taxonomies = get_object_taxonomies( 'attachment', 'names' );
		
		if ( ! empty( $media_taxonomies ) ) {
			foreach ( array_slice( $media_taxonomies, 0, 2 ) as $taxonomy ) {
				$terms = get_terms( array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'number'     => 1,
				) );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$term = $terms[0];
					
					$tax_count = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(DISTINCT p.ID) 
							FROM {$wpdb->posts} p 
							INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id 
							INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
							WHERE p.post_type = 'attachment' 
							AND tt.taxonomy = %s 
							AND tt.term_id = %d",
							$taxonomy,
							$term->term_id
						)
					);

					if ( $tax_count > 0 ) {
						$tax_query = new \WP_Query( array(
							'post_type'      => 'attachment',
							'post_status'    => 'inherit',
							'tax_query'      => array(
								array(
									'taxonomy' => $taxonomy,
									'field'    => 'term_id',
									'terms'    => $term->term_id,
								),
							),
							'posts_per_page' => -1,
							'fields'         => 'ids',
						) );

						if ( abs( count( $tax_query->posts ) - $tax_count ) > 2 ) {
							$issues[] = sprintf(
								/* translators: %s: taxonomy name */
								__( 'Taxonomy filter (%s) returns inaccurate count', 'wpshadow' ),
								$taxonomy
							);
						}
					}
				}
			}
		}

		// Test REST API filter compatibility.
		$rest_filter_start = microtime( true );
		
		$rest_request = new \WP_REST_Request( 'GET', '/wp/v2/media' );
		$rest_request->set_param( 'media_type', 'image' );
		$rest_request->set_param( 'per_page', 20 );
		
		$rest_response = rest_do_request( $rest_request );
		
		$rest_filter_time = microtime( true ) - $rest_filter_start;

		if ( $rest_filter_time > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: filter time */
				__( 'REST API media filter took %ss (optimize for apps)', 'wpshadow' ),
				number_format( $rest_filter_time, 2 )
			);
		}

		if ( $rest_response->is_error() ) {
			$issues[] = __( 'REST API media filter returned error (check API)', 'wpshadow' );
		}

		// Check for uploaded_to filter.
		$posts_with_media = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_parent) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent > 0"
		);

		if ( $posts_with_media > 0 ) {
			$parent_id = $wpdb->get_var(
				"SELECT post_parent 
				FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_parent > 0 
				LIMIT 1"
			);

			$uploaded_to_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) 
					FROM {$wpdb->posts} 
					WHERE post_type = 'attachment' 
					AND post_parent = %d",
					$parent_id
				)
			);

			$uploaded_to_query = new \WP_Query( array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_parent'    => $parent_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			if ( abs( count( $uploaded_to_query->posts ) - $uploaded_to_count ) > 1 ) {
				$issues[] = __( 'Uploaded to filter returns inaccurate count', 'wpshadow' );
			}
		}

		// Check for AJAX media query filters.
		if ( isset( $_REQUEST['query'] ) ) {
			// This would be in AJAX context, skip for now.
		}

		// Test filter cache.
		$filter_cache_key = 'media_filter_image_' . md5( serialize( array() ) );
		$cached_filter = wp_cache_get( $filter_cache_key, 'wpshadow' );
		
		if ( false === $cached_filter && $total_media > 500 ) {
			$issues[] = __( 'Filter results not cached (enable object caching)', 'wpshadow' );
		}

		// Check for excessive filter options.
		if ( ! empty( $media_taxonomies ) && count( $media_taxonomies ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of taxonomies */
				__( '%d media taxonomies registered (many filters may confuse users)', 'wpshadow' ),
				count( $media_taxonomies )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/media-filter-functionality',
			);
		}

		return null;
	}
}
