<?php
/**
 * Image Quality Settings Diagnostic
 *
 * Checks JPEG quality settings and compression levels. Balances quality vs file size.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Quality_Settings Class
 *
 * Validates image quality settings for uploads. WordPress applies JPEG
 * compression (default 82% quality). Settings too high waste space,
 * too low reduce visual quality.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Image_Quality_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-quality-settings';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Quality Settings';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks JPEG quality settings and compression levels';

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
	 * - JPEG quality settings
	 * - PNG compression levels
	 * - WebP quality settings
	 * - File size implications
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get current JPEG quality setting.
		$jpeg_quality = apply_filters( 'jpeg_quality', 82, 'image_resize' );
		$wp_editor_quality = apply_filters( 'wp_editor_set_quality', $jpeg_quality );

		// Check if quality is too low.
		if ( $jpeg_quality < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: quality percentage */
				__( 'JPEG quality (%d%%) is very low - images may look poor', 'wpshadow' ),
				$jpeg_quality
			);
		}

		// Check if quality is too high (wastes space).
		if ( $jpeg_quality > 95 ) {
			$issues[] = sprintf(
				/* translators: %d: quality percentage */
				__( 'JPEG quality (%d%%) is very high - creates unnecessarily large files', 'wpshadow' ),
				$jpeg_quality
			);
		}

		// Check if quality differs between contexts.
		if ( $jpeg_quality !== $wp_editor_quality ) {
			$issues[] = sprintf(
				/* translators: 1: default quality, 2: editor quality */
				__( 'JPEG quality differs between contexts (resize: %1$d%%, editor: %2$d%%) - inconsistent output', 'wpshadow' ),
				$jpeg_quality,
				$wp_editor_quality
			);
		}

		// Check WebP quality if supported.
		if ( function_exists( 'imagewebp' ) ) {
			$webp_quality = apply_filters( 'webp_uploads_upload_image_mime_transforms', $jpeg_quality );
			
			if ( is_numeric( $webp_quality ) && $webp_quality < 70 ) {
				$issues[] = sprintf(
					/* translators: %d: quality percentage */
					__( 'WebP quality (%d%%) is low - reduces modern browser image quality', 'wpshadow' ),
					$webp_quality
				);
			}
		}

		// Check PNG compression level (if GD available).
		if ( extension_loaded( 'gd' ) && function_exists( 'imagepng' ) ) {
			// PNG compression is 0-9 (WordPress uses filter, but defaults vary).
			$png_compression = apply_filters( 'image_save_png_compression_level', 6 );
			
			if ( $png_compression < 0 || $png_compression > 9 ) {
				$issues[] = sprintf(
					/* translators: %d: compression level */
					__( 'PNG compression level (%d) is invalid (should be 0-9)', 'wpshadow' ),
					$png_compression
				);
			}

			if ( $png_compression < 3 ) {
				$issues[] = sprintf(
					/* translators: %d: compression level */
					__( 'PNG compression level (%d) is low - creates larger files', 'wpshadow' ),
					$png_compression
				);
			}
		}

		// Analyze actual file sizes to detect quality issues.
		global $wpdb;

		// Get average file sizes for recent images.
		$avg_sizes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					SUBSTRING_INDEX(p.post_mime_type, '/', -1) as format,
					AVG(LENGTH(pm.meta_value)) as avg_size,
					COUNT(*) as count
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				GROUP BY format",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		foreach ( $avg_sizes as $format_data ) {
			$format   = $format_data->format;
			$avg_size = (int) $format_data->avg_size;
			$count    = (int) $format_data->count;

			// Expected ranges (rough estimates).
			$expected_ranges = array(
				'jpeg' => array( 'min' => 50000, 'max' => 500000 ), // 50KB - 500KB.
				'png'  => array( 'min' => 100000, 'max' => 1000000 ), // 100KB - 1MB.
				'gif'  => array( 'min' => 10000, 'max' => 500000 ), // 10KB - 500KB.
				'webp' => array( 'min' => 30000, 'max' => 300000 ), // 30KB - 300KB.
			);

			if ( isset( $expected_ranges[ $format ] ) ) {
				$range = $expected_ranges[ $format ];
				
				if ( $avg_size > $range['max'] ) {
					$issues[] = sprintf(
						/* translators: 1: format, 2: average size */
						__( 'Average %1$s file size (%2$s) is very large - consider lowering quality', 'wpshadow' ),
						strtoupper( $format ),
						size_format( $avg_size )
					);
				}

				if ( $avg_size < $range['min'] && $count > 5 ) {
					$issues[] = sprintf(
						/* translators: 1: format, 2: average size */
						__( 'Average %1$s file size (%2$s) is very small - quality may be too low', 'wpshadow' ),
						strtoupper( $format ),
						size_format( $avg_size )
					);
				}
			}
		}

		// Check for filters modifying quality.
		$quality_filters = array(
			'jpeg_quality'                        => __( 'JPEG quality', 'wpshadow' ),
			'wp_editor_set_quality'               => __( 'Editor quality', 'wpshadow' ),
			'image_save_png_compression_level'    => __( 'PNG compression', 'wpshadow' ),
			'webp_uploads_upload_image_mime_transforms' => __( 'WebP quality', 'wpshadow' ),
		);

		$active_filters = array();
		foreach ( $quality_filters as $filter_name => $description ) {
			if ( has_filter( $filter_name ) ) {
				$active_filters[] = $description;
			}
		}

		if ( ! empty( $active_filters ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of filters */
				__( 'Active quality filters detected: %s - verify settings are optimal', 'wpshadow' ),
				implode( ', ', $active_filters )
			);
		}

		// Check for optimization plugins that might conflict.
		$active_plugins = get_option( 'active_plugins', array() );
		$optimization_plugins = array(
			'ewww-image-optimizer' => __( 'EWWW Image Optimizer - may override quality settings', 'wpshadow' ),
			'imagify'              => __( 'Imagify - may apply additional compression', 'wpshadow' ),
			'smush'                => __( 'Smush - may alter quality settings', 'wpshadow' ),
			'shortpixel'           => __( 'ShortPixel - may modify compression', 'wpshadow' ),
			'optimole'             => __( 'Optimole - may serve different quality', 'wpshadow' ),
		);

		foreach ( $optimization_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = $message;
					break;
				}
			}
		}

		// Check ImageMagick quality settings.
		if ( class_exists( 'Imagick' ) ) {
			try {
				$imagick = new \Imagick();
				$quality = $imagick->getImageCompressionQuality();
				
				if ( $quality > 0 && $quality !== $jpeg_quality ) {
					$issues[] = sprintf(
						/* translators: 1: ImageMagick quality, 2: WordPress quality */
						__( 'ImageMagick compression quality (%1$d) differs from WordPress setting (%2$d)', 'wpshadow' ),
						$quality,
						$jpeg_quality
					);
				}
			} catch ( \Exception $e ) {
				// Silently fail - not critical.
			}
		}

		// Check for very large images that should be downscaled.
		$large_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND pm.meta_value LIKE %s
				AND p.post_date > %s",
				'image/%',
				'%width";i:%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		// Parse metadata to check actual dimensions (sample).
		$oversized = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_metadata'
				WHERE p.post_type = 'attachment'
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				LIMIT 10",
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		$oversized_count = 0;
		foreach ( $oversized as $image ) {
			$metadata = maybe_unserialize( $image->meta_value );
			if ( isset( $metadata['width'] ) && $metadata['width'] > 2560 ) {
				$oversized_count++;
			}
		}

		if ( $oversized_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image exceeds 2560px width - consider enabling automatic downscaling',
					'%d recent images exceed 2560px width - consider enabling automatic downscaling',
					$oversized_count,
					'wpshadow'
				),
				$oversized_count
			);
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with image quality settings',
						'%d issues detected with image quality settings',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-quality-settings',
				'details'      => array(
					'issues'          => $issues,
					'jpeg_quality'    => $jpeg_quality,
					'editor_quality'  => $wp_editor_quality,
					'active_filters'  => $active_filters,
					'oversized_count' => $oversized_count,
				),
			);
		}

		return null;
	}
}
