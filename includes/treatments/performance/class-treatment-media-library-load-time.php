<?php
/**
 * Media Library Load Time Treatment
 *
 * Tests media library performance by measuring load times for
 * grid and list views with various attachment counts.
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
 * Treatment_Media_Library_Load_Time Class
 *
 * Ensures media library loads quickly even with large numbers
 * of attachments and identifies performance bottlenecks.
 *
 * @since 1.6033.1545
 */
class Treatment_Media_Library_Load_Time extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-library-load-time';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Library Load Time';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media library performance and load times';

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
		$issues = array();

		// Count total attachments.
		global $wpdb;
		$attachment_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);

		if ( $attachment_count > 10000 ) {
			$issues[] = sprintf(
				/* translators: %s: formatted number of attachments */
				__( 'Media library contains %s attachments; performance may degrade', 'wpshadow' ),
				number_format_i18n( $attachment_count )
			);
		}

		// Test query performance for recent attachments.
		$start_time = microtime( true );
		
		$query_args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'inherit',
		);
		
		$query = new \WP_Query( $query_args );
		
		$query_time = microtime( true ) - $start_time;

		// If query takes more than 2 seconds, flag as slow.
		if ( $query_time > 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Media library query took %s seconds; consider database optimization', 'wpshadow' ),
				number_format( $query_time, 2 )
			);
		}

		// Check for unattached media.
		$unattached_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_parent = 0"
		);

		if ( $unattached_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %s: formatted number of unattached files */
				__( '%s unattached media files detected; cleaning up can improve performance', 'wpshadow' ),
				number_format_i18n( $unattached_count )
			);
		}

		// Check for orphaned thumbnails (attachment meta exists but file doesn't).
		$recent_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 50,
				'orderby'        => 'rand',
			)
		);

		$orphaned_count = 0;
		foreach ( $recent_attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );
			if ( ! empty( $file_path ) && ! file_exists( $file_path ) ) {
				$orphaned_count++;
			}
		}

		if ( $orphaned_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned records */
				__( 'Found %d orphaned attachment records (files missing); database cleanup recommended', 'wpshadow' ),
				$orphaned_count
			);
		}

		// Check for missing image sizes in metadata.
		$missing_sizes_count = 0;
		foreach ( array_slice( $recent_attachments, 0, 10 ) as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			
			if ( empty( $metadata['sizes'] ) && wp_attachment_is_image( $attachment->ID ) ) {
				$missing_sizes_count++;
			}
		}

		if ( $missing_sizes_count > 3 ) {
			$issues[] = __( 'Several images are missing thumbnail sizes; regenerate thumbnails to improve performance', 'wpshadow' );
		}

		// Check if media modal scripts are optimized.
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script not registered; media library UI may not function properly', 'wpshadow' );
		}

		// Check for pagination in media grid.
		if ( $attachment_count > 200 ) {
			// Check if infinite scroll or pagination is configured.
			$upload_per_page = get_user_meta( get_current_user_id(), 'upload_per_page', true );
			
			if ( empty( $upload_per_page ) || $upload_per_page > 100 ) {
				$issues[] = __( 'Media grid may load too many items at once; configure pagination for better performance', 'wpshadow' );
			}
		}

		// Check database indexes.
		$indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->posts} WHERE Column_name = %s",
				'post_type'
			)
		);

		if ( empty( $indexes ) ) {
			$issues[] = __( 'Missing post_type index on posts table; media queries may be slow', 'wpshadow' );
		}

		// Test thumbnail generation speed.
		$test_attachments = array_slice( $recent_attachments, 0, 3 );
		$slow_thumbnail_count = 0;

		foreach ( $test_attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );
			
			if ( file_exists( $file_path ) && wp_attachment_is_image( $attachment->ID ) ) {
				$start = microtime( true );
				$editor = wp_get_image_editor( $file_path );
				
				if ( ! is_wp_error( $editor ) ) {
					$editor->resize( 150, 150, true );
					$time = microtime( true ) - $start;
					
					// If thumbnail generation takes more than 3 seconds, flag it.
					if ( $time > 3.0 ) {
						$slow_thumbnail_count++;
					}
				}
			}
		}

		if ( $slow_thumbnail_count > 1 ) {
			$issues[] = __( 'Thumbnail generation is slow; check server resources and image editor configuration', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-library-performance',
			);
		}

		return null;
	}
}
