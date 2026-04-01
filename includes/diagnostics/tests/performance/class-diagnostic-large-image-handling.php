<?php
/**
 * Large Image Handling Diagnostic
 *
 * Tests handling of very large images (dimensions and file size). Detects memory issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Large_Image_Handling Class
 *
 * Validates large image handling. WordPress 5.3+ introduced "big image"
 * threshold (default 2560px) to automatically downscale large uploads.
 * Very large images can cause memory exhaustion and slow page loads.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Large_Image_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'large-image-handling';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Large Image Handling';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of very large images';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Big image threshold configuration
	 * - Memory limits for large images
	 * - Existing oversized images
	 * - File size limits
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if big_image_size_threshold exists (WP 5.3+).
		$threshold = apply_filters( 'big_image_size_threshold', 2560 );

		// Check if threshold is disabled.
		if ( false === $threshold ) {
			$issues[] = __( 'big_image_size_threshold is disabled - very large images will not be downscaled', 'wpshadow' );
		}

		// Check if threshold is too high.
		if ( is_numeric( $threshold ) && $threshold > 5000 ) {
			$issues[] = sprintf(
				/* translators: %d: threshold pixels */
				__( 'big_image_size_threshold (%dpx) is very high - may cause memory issues', 'wpshadow' ),
				$threshold
			);
		}

		// Check memory limit for large image processing.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		// Calculate needed memory for different image sizes.
		// Formula: width × height × 4 (RGBA) × safety factor (2-3x)
		$threshold_memory = is_numeric( $threshold ) ? ( $threshold * $threshold * 4 * 3 ) : 0;

		if ( $memory_limit > 0 && $threshold_memory > 0 && $memory_limit < $threshold_memory ) {
			$issues[] = sprintf(
				/* translators: 1: memory limit, 2: estimated need */
				__( 'memory_limit (%1$s) may be insufficient for big_image_size_threshold (%2$s estimated)', 'wpshadow' ),
				size_format( $memory_limit ),
				size_format( $threshold_memory )
			);
		}

		// Recommended minimum for large images.
		$recommended_memory = 256 * 1024 * 1024; // 256MB.
		if ( $memory_limit > 0 && $memory_limit < $recommended_memory ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit (%s) is low - recommend 256MB+ for large image processing', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for existing very large images.
		global $wpdb;

		$large_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				LIMIT 50",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-60 days' ) )
			)
		);

		$oversized_count = 0;
		$oversized_details = array();

		foreach ( $large_images as $image ) {
			$metadata = maybe_unserialize( $image->meta_value );

			if ( ! is_array( $metadata ) || ! isset( $metadata['width'] ) || ! isset( $metadata['height'] ) ) {
				continue;
			}

			$width = (int) $metadata['width'];
			$height = (int) $metadata['height'];

			// Check against threshold.
			if ( is_numeric( $threshold ) && ( $width > $threshold || $height > $threshold ) ) {
				$oversized_count++;

				if ( count( $oversized_details ) < 10 ) {
					$oversized_details[] = array(
						'id'     => $image->ID,
						'width'  => $width,
						'height' => $height,
					);
				}
			}
		}

		if ( $oversized_count > 0 ) {
			$issues[] = sprintf(
				/* translators: 1: number of images, 2: threshold */
				_n(
					'%1$d image exceeds big_image_size_threshold (%2$dpx)',
					'%1$d images exceed big_image_size_threshold (%2$dpx)',
					$oversized_count,
					'wpshadow'
				),
				$oversized_count,
				$threshold
			);
		}

		// Check upload file size limit.
		$upload_max = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		$wp_max = wp_max_upload_size();

		// Very large images (20MP+) can be 10-30MB as JPEG.
		$large_image_size = 30 * 1024 * 1024; // 30MB.

		if ( $wp_max < $large_image_size ) {
			$issues[] = sprintf(
				/* translators: %s: max size */
				__( 'WordPress max upload size (%s) may prevent large image uploads', 'wpshadow' ),
				size_format( $wp_max )
			);
		}

		// Check for -scaled images (WordPress creates these).
		$scaled_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_mime_type LIKE %s
				AND guid LIKE %s",
				'image/%',
				'%-scaled.%'
			)
		);

		if ( $scaled_images > 0 ) {
			// This is actually good - WordPress is scaling down large images.
			$issues[] = sprintf(
				/* translators: %d: number of scaled images */
				_n(
					'%d image was automatically scaled down (this is good)',
					'%d images were automatically scaled down (this is good)',
					$scaled_images,
					'wpshadow'
				),
				$scaled_images
			);
		}

		// Check for original_image in metadata (WP 5.3+).
		$has_originals = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attachment_metadata'
				AND meta_value LIKE %s",
				'%original_image%'
			)
		);

		if ( $has_originals > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image has original stored separately (scaled version in use)',
					'%d images have originals stored separately (scaled versions in use)',
					$has_originals,
					'wpshadow'
				),
				$has_originals
			);
		}

		// Check max_execution_time (large images take time to process).
		$max_execution = (int) ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 90 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_execution_time (%d seconds) is low - large image processing may timeout', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for filters disabling downscaling.
		if ( has_filter( 'big_image_size_threshold' ) ) {
			$issues[] = __( 'big_image_size_threshold filter is active - verify it\'s not disabling downscaling', 'wpshadow' );
		}

		// Check image library support for large images.
		$has_imagick = class_exists( 'Imagick' );

		if ( $has_imagick ) {
			try {
				$imagick = new \Imagick();

				// Check memory resource limit.
				$im_memory_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_MEMORY );

				if ( $im_memory_limit > 0 && $im_memory_limit < ( 512 * 1024 * 1024 ) ) {
					$issues[] = sprintf(
						/* translators: %s: memory limit */
						__( 'ImageMagick memory limit (%s) is low for large images', 'wpshadow' ),
						size_format( $im_memory_limit )
					);
				}

				// Check width/height limits.
				$im_width_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_WIDTH );
				$im_height_limit = $imagick->getResourceLimit( \Imagick::RESOURCETYPE_HEIGHT );

				if ( $im_width_limit > 0 && is_numeric( $threshold ) && $im_width_limit < $threshold ) {
					$issues[] = sprintf(
						/* translators: %d: width limit */
						__( 'ImageMagick width limit (%dpx) is less than big_image_size_threshold', 'wpshadow' ),
						$im_width_limit
					);
				}
			} catch ( \Exception $e ) {
				// Silently fail.
			}
		}

		// Check for very large file sizes.
		$large_files = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				LIMIT 20",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		$large_file_count = 0;
		$upload_dir = wp_upload_dir();

		foreach ( $large_files as $file ) {
			$file_path = $upload_dir['basedir'] . '/' . $file->file_path;

			if ( file_exists( $file_path ) ) {
				$size = filesize( $file_path );

				// Flag files over 5MB (unusually large for web).
				if ( $size > 5 * 1024 * 1024 ) {
					$large_file_count++;
				}
			}
		}

		if ( $large_file_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent image file is over 5MB - may slow page loads',
					'%d recent image files are over 5MB - may slow page loads',
					$large_file_count,
					'wpshadow'
				),
				$large_file_count
			);
		}

		// Check for plugins that might interfere.
		$active_plugins = get_option( 'active_plugins', array() );
		$image_plugins = array(
			'disable-big-image-threshold' => __( 'Explicitly disables automatic downscaling', 'wpshadow' ),
		);

		foreach ( $image_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: message */
						__( 'Plugin conflict: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with large image handling',
						'%d issues detected with large image handling',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/large-image-handling?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'            => $issues,
					'threshold'         => $threshold,
					'memory_limit'      => size_format( $memory_limit ),
					'oversized_count'   => $oversized_count,
					'oversized_details' => $oversized_details,
					'scaled_images'     => $scaled_images,
					'large_file_count'  => $large_file_count,
				),
			);
		}

		return null;
	}
}
