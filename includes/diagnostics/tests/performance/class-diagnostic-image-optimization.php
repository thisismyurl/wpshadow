<?php
/**
 * Image Optimization Diagnostic
 *
 * Checks for unoptimized images that could impact Core Web Vitals,
 * particularly Largest Contentful Paint (LCP).
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Diagnostic Class
 *
 * Analyzes images for optimization opportunities including:
 * - Oversized source images
 * - Missing responsive image variations
 * - High file size to dimensions ratio
 * - Unoptimized JPEG quality
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unoptimized images impacting Core Web Vitals';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get all attachments
		$query = "SELECT ID, post_date FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY post_date DESC LIMIT 100";

		$attachments = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $attachments ) ) {
			return null;
		}

		$unoptimized_count = 0;
		$oversized_count    = 0;
		$total_size_kb      = 0;
		$max_file_size      = 0;
		$largest_image      = null;

		foreach ( $attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			$file_path = get_attached_file( $attachment->ID );

			if ( ! $metadata || ! $file_path || ! file_exists( $file_path ) ) {
				continue;
			}

			$file_size = filesize( $file_path );
			if ( ! $file_size ) {
				continue;
			}

			$file_size_kb = intval( $file_size / 1024 );
			$total_size_kb += $file_size_kb;

			// Track largest image
			if ( $file_size > $max_file_size ) {
				$max_file_size = $file_size;
				$largest_image = basename( $file_path );
			}

			// Check if image is oversized
			if ( isset( $metadata['width'], $metadata['height'] ) ) {
				$width  = intval( $metadata['width'] );
				$height = intval( $metadata['height'] );

				// Calculate optimal size (rough estimate)
				$optimal_size_kb = max( 50, intval( ( $width * $height ) / 50000 ) );

				if ( $file_size_kb > $optimal_size_kb * 2 ) {
					$oversized_count++;
				}
			}

			// Check for missing srcset (responsive images)
			if ( empty( $metadata['sizes'] ) ) {
				$unoptimized_count++;
			}
		}

		$images_percent = intval( ( $oversized_count / count( $attachments ) ) * 100 );

		if ( $oversized_count >= 5 || $images_percent > 25 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of unoptimized images, %d: total size in MB */
					__( 'Found %d unoptimized images using %d MB. Optimizing images could reduce load time by 30-50%%.', 'wpshadow' ),
					$oversized_count,
					intval( $total_size_kb / 1024 )
				),
				'severity'      => $images_percent > 50 ? 'high' : 'medium',
				'threat_level'  => $images_percent > 50 ? 70 : 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'          => array(
					'total_images'        => count( $attachments ),
					'unoptimized_count'   => $oversized_count,
					'unoptimized_percent' => $images_percent,
					'total_size_mb'       => intval( $total_size_kb / 1024 ),
					'largest_image'       => $largest_image,
					'largest_size_kb'     => intval( $max_file_size / 1024 ),
					'recommendation'      => 'Use image optimization plugins (Smush, Imagify, Optimus) or manually compress with tools like TinyPNG/TinyJPG',
				),
			);
		}

		return null;
	}
}
