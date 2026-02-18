<?php
/**
 * Media Large Image Handling Diagnostic
 *
 * Tests handling of very large images (dimensions and file size)
 * and detects potential memory issues during processing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1545
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Large_Image_Handling Class
 *
 * Ensures WordPress can handle large images without running out
 * of memory or timing out during upload and processing.
 *
 * @since 1.6033.1545
 */
class Diagnostic_Media_Large_Image_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-large-image-handling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Large Image Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of large images and detects memory issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get memory limit.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$wp_memory_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );

		// Check if memory limit is adequate for large images.
		$recommended_memory = 256 * MB_IN_BYTES; // 256MB recommended.
		
		if ( $memory_limit < $recommended_memory && $wp_memory_limit < $recommended_memory ) {
			$issues[] = sprintf(
				/* translators: 1: current memory limit, 2: recommended memory limit */
				__( 'Memory limit (%1$s) may be insufficient for processing large images; recommend at least %2$s', 'wpshadow' ),
				size_format( max( $memory_limit, $wp_memory_limit ) ),
				size_format( $recommended_memory )
			);
		}

		// Check max upload file size.
		$max_upload = wp_max_upload_size();
		$post_max_size = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		$upload_max_filesize = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );

		if ( $max_upload < 10 * MB_IN_BYTES ) {
			$issues[] = sprintf(
				/* translators: %s: current upload size limit */
				__( 'Maximum upload size (%s) is very restrictive; users cannot upload high-resolution images', 'wpshadow' ),
				size_format( $max_upload )
			);
		}

		// Check if big image size threshold is enabled.
		$big_image_threshold = apply_filters( 'big_image_size_threshold', 2560 );
		
		if ( false === $big_image_threshold ) {
			$issues[] = __( 'Big image size threshold is disabled; very large images will not be automatically scaled down', 'wpshadow' );
		} elseif ( $big_image_threshold > 4096 ) {
			$issues[] = sprintf(
				/* translators: %d: threshold in pixels */
				__( 'Big image size threshold (%dpx) is very high; may cause memory issues with large uploads', 'wpshadow' ),
				$big_image_threshold
			);
		}

		// Check execution time limit.
		$max_execution_time = (int) ini_get( 'max_execution_time' );
		
		if ( $max_execution_time > 0 && $max_execution_time < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'Max execution time (%d seconds) may be too short for processing large images', 'wpshadow' ),
				$max_execution_time
			);
		}

		// Check for recent large image uploads.
		global $wpdb;
		$large_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE meta_key = '_wp_attached_file' 
				AND meta_value LIKE %s 
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts} 
					WHERE post_type = 'attachment' 
					AND post_mime_type LIKE 'image/%'
					AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
				)",
				'%.jpg'
			)
		);

		if ( $large_images > 0 ) {
			// Get file sizes of recent uploads.
			$upload_dir = wp_upload_dir();
			$recent_uploads = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'posts_per_page' => 5,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);

			$oversized_count = 0;
			foreach ( $recent_uploads as $upload ) {
				$file_path = get_attached_file( $upload->ID );
				if ( file_exists( $file_path ) ) {
					$file_size = filesize( $file_path );
					// Flag images over 5MB as potentially problematic.
					if ( $file_size > 5 * MB_IN_BYTES ) {
						$oversized_count++;
					}
				}
			}

			if ( $oversized_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of images */
					_n(
						'%d recent upload is over 5MB; may cause performance issues',
						'%d recent uploads are over 5MB; may cause performance issues',
						$oversized_count,
						'wpshadow'
					),
					$oversized_count
				);
			}
		}

		// Check if image subsizes are being generated.
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			$issues[] = __( 'wp_generate_attachment_metadata function not available; thumbnails may not be generated', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/media-large-image-handling',
			);
		}

		return null;
	}
}
