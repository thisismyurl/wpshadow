<?php
/**
 * Post Thumbnail Display Diagnostic
 *
 * Checks if post thumbnails display correctly on frontend. Tests image size
 * generation and featured image functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Thumbnail Display Diagnostic Class
 *
 * Checks for issues with post thumbnail functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_Thumbnail_Display extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-thumbnail-display';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Thumbnail Display';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post thumbnails display correctly and image sizes are properly generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if theme supports post-thumbnails.
		$theme_support = current_theme_supports( 'post-thumbnails' );
		
		if ( ! $theme_support ) {
			$issues[] = __( 'Theme does not support post thumbnails (featured images disabled)', 'wpshadow' );
		}

		// Get posts with featured images.
		$posts_with_thumbnails = $wpdb->get_results(
			"SELECT p.ID, p.post_title, pm.meta_value as thumbnail_id
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_thumbnail_id'
			AND p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			LIMIT 100",
			ARRAY_A
		);

		if ( empty( $posts_with_thumbnails ) ) {
			return null; // No featured images to check.
		}

		// Check for broken thumbnail references (attachment doesn't exist).
		$broken_thumbnails = 0;
		$missing_files = 0;
		$missing_metadata = 0;

		foreach ( $posts_with_thumbnails as $post ) {
			$thumbnail_id = (int) $post['thumbnail_id'];

			// Check if attachment exists.
			$attachment = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT ID, post_mime_type FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'attachment'",
					$thumbnail_id
				)
			);

			if ( ! $attachment ) {
				++$broken_thumbnails;
				continue;
			}

			// Check if attachment file exists.
			$file_path = get_attached_file( $thumbnail_id );
			if ( $file_path && ! file_exists( $file_path ) ) {
				++$missing_files;
			}

			// Check if attachment has metadata.
			$metadata = wp_get_attachment_metadata( $thumbnail_id );
			if ( empty( $metadata ) ) {
				++$missing_metadata;
			}
		}

		if ( $broken_thumbnails > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with broken thumbnails */
				__( '%d posts reference deleted featured images (broken thumbnails)', 'wpshadow' ),
				$broken_thumbnails
			);
		}

		if ( $missing_files > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of missing thumbnail files */
				__( '%d featured image files are missing from disk (display failures)', 'wpshadow' ),
				$missing_files
			);
		}

		if ( $missing_metadata > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of thumbnails without metadata */
				__( '%d featured images lack metadata (sizes not generated)', 'wpshadow' ),
				$missing_metadata
			);
		}

		// Check for registered image sizes.
		global $_wp_additional_image_sizes;
		$image_sizes = get_intermediate_image_sizes();
		
		if ( empty( $image_sizes ) ) {
			$issues[] = __( 'No intermediate image sizes registered (thumbnails may not resize)', 'wpshadow' );
		}

		// Check if thumbnails are being generated properly.
		$sample_thumbnail = ! empty( $posts_with_thumbnails ) ? (int) $posts_with_thumbnails[0]['thumbnail_id'] : 0;
		
		if ( $sample_thumbnail > 0 ) {
			$metadata = wp_get_attachment_metadata( $sample_thumbnail );
			
			if ( ! empty( $metadata ) && isset( $metadata['sizes'] ) ) {
				$generated_sizes = count( $metadata['sizes'] );
				$registered_sizes = count( $image_sizes );
				
				// If significantly fewer sizes generated than registered, there's an issue.
				if ( $generated_sizes < ( $registered_sizes / 2 ) && $registered_sizes > 3 ) {
					$issues[] = sprintf(
						/* translators: 1: generated sizes, 2: registered sizes */
						__( 'Only %1$d of %2$d image sizes generated (regeneration needed)', 'wpshadow' ),
						$generated_sizes,
						$registered_sizes
					);
				}
			}
		}

		// Check for excessive image sizes (performance issue).
		if ( count( $image_sizes ) > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of image sizes */
				__( '%d image sizes registered (excessive, impacts upload performance)', 'wpshadow' ),
				count( $image_sizes )
			);
		}

		// Check for posts with invalid thumbnail IDs (non-numeric, zero).
		$invalid_thumbnail_ids = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_thumbnail_id'
			AND (meta_value = '0' OR meta_value = '' OR meta_value IS NULL)"
		);

		if ( $invalid_thumbnail_ids > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with invalid IDs */
				__( '%d posts have invalid featured image IDs (empty or zero)', 'wpshadow' ),
				$invalid_thumbnail_ids
			);
		}

		// Check for duplicate thumbnail assignments (same image on many posts).
		$duplicate_thumbnails = $wpdb->get_results(
			"SELECT meta_value, COUNT(*) as usage_count
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_thumbnail_id'
			AND meta_value != '0'
			GROUP BY meta_value
			HAVING usage_count > 50
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $duplicate_thumbnails ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of images used excessively */
				__( '%d featured images used on 50+ posts (consider unique images)', 'wpshadow' ),
				count( $duplicate_thumbnails )
			);
		}

		// Check if post thumbnail support is limited to specific post types.
		if ( $theme_support ) {
			$supported_types = get_theme_support( 'post-thumbnails' );
			if ( is_array( $supported_types ) && ! empty( $supported_types[0] ) && is_array( $supported_types[0] ) ) {
				$limited_types = $supported_types[0];
				$all_types = get_post_types( array( 'public' => true ) );
				
				if ( count( $limited_types ) < count( $all_types ) ) {
					$issues[] = sprintf(
						/* translators: 1: supported types, 2: total types */
						__( 'Featured images only enabled for %1$d of %2$d post types', 'wpshadow' ),
						count( $limited_types ),
						count( $all_types )
					);
				}
			}
		}

		// Check for thumbnail_id meta on non-existent posts.
		$orphaned_thumbnail_meta = $wpdb->get_var(
			"SELECT COUNT(pm.meta_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_thumbnail_id'
			AND p.ID IS NULL"
		);

		if ( $orphaned_thumbnail_meta > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned meta entries */
				__( '%d orphaned featured image meta entries (database cleanup needed)', 'wpshadow' ),
				$orphaned_thumbnail_meta
			);
		}

		// Check for very large featured images (not optimized).
		$large_thumbnails = $wpdb->get_results(
			"SELECT p.ID, pm.meta_value as thumbnail_id
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_thumbnail_id'
			AND p.post_status = 'publish'
			LIMIT 20",
			ARRAY_A
		);

		$oversized_count = 0;
		foreach ( $large_thumbnails as $post ) {
			$thumbnail_id = (int) $post['thumbnail_id'];
			$file_path = get_attached_file( $thumbnail_id );
			
			if ( $file_path && file_exists( $file_path ) ) {
				$file_size = filesize( $file_path );
				// Flag images over 2MB as featured images.
				if ( $file_size > 2097152 ) {
					++$oversized_count;
				}
			}
		}

		if ( $oversized_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of oversized images */
				__( '%d featured images over 2MB (should be optimized)', 'wpshadow' ),
				$oversized_count
			);
		}

		// Check if has_post_thumbnail() filter is heavily modified.
		$thumbnail_filters = $GLOBALS['wp_filter']['has_post_thumbnail'] ?? null;
		if ( $thumbnail_filters && count( $thumbnail_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on has_post_thumbnail (may cause display issues)', 'wpshadow' ),
				count( $thumbnail_filters->callbacks )
			);
		}

		// Check if get_post_thumbnail_id filter is breaking things.
		$thumbnail_id_filters = $GLOBALS['wp_filter']['get_post_thumbnail_id'] ?? null;
		if ( $thumbnail_id_filters && count( $thumbnail_id_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on get_post_thumbnail_id (potential conflicts)', 'wpshadow' ),
				count( $thumbnail_id_filters->callbacks )
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
				'kb_link'     => 'https://wpshadow.com/kb/post-thumbnail-display',
			);
		}

		return null;
	}
}
