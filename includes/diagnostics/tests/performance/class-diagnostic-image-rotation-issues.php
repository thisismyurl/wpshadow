<?php
/**
 * Image Rotation Issues Diagnostic
 *
 * Tests EXIF orientation handling. Detects images displaying sideways/upside-down.
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
 * Diagnostic_Image_Rotation_Issues Class
 *
 * Validates EXIF orientation handling. Digital cameras store rotation info
 * in EXIF data. WordPress should auto-rotate images based on EXIF orientation,
 * but this requires proper image library support and configuration.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Image_Rotation_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-rotation-issues';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Rotation Issues';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests EXIF orientation handling';

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
	 * - EXIF extension availability
	 * - Image library rotation support
	 * - WordPress auto-rotation functionality
	 * - Images with rotation metadata
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if EXIF extension is loaded.
		$has_exif = extension_loaded( 'exif' );
		
		if ( ! $has_exif ) {
			$issues[] = __( 'PHP EXIF extension not loaded - cannot read image orientation data', 'wpshadow' );
		}

		// Check if exif_read_data function exists.
		if ( ! function_exists( 'exif_read_data' ) ) {
			$issues[] = __( 'exif_read_data() function not available - EXIF orientation cannot be detected', 'wpshadow' );
		}

		// Check image library rotation capabilities.
		$has_gd = extension_loaded( 'gd' );
		$has_imagick = class_exists( 'Imagick' );

		// GD rotation support.
		if ( $has_gd ) {
			if ( ! function_exists( 'imagerotate' ) ) {
				$issues[] = __( 'GD library missing imagerotate() function - cannot auto-rotate images', 'wpshadow' );
			}
		}

		// ImageMagick rotation support.
		$imagick_can_rotate = false;
		if ( $has_imagick ) {
			try {
				$imagick = new \Imagick();
				if ( method_exists( $imagick, 'rotateImage' ) ) {
					$imagick_can_rotate = true;
				}
			} catch ( \Exception $e ) {
				// Silently fail.
			}
		}

		// Neither library can rotate.
		if ( $has_gd && ! function_exists( 'imagerotate' ) && ! $imagick_can_rotate ) {
			$issues[] = __( 'No image library supports rotation - images will display with incorrect orientation', 'wpshadow' );
		}

		// Check if WordPress image rotation filter is active.
		$rotation_filter = has_filter( 'wp_image_maybe_exif_rotate' );
		
		if ( false === $rotation_filter ) {
			$issues[] = __( 'wp_image_maybe_exif_rotate filter not detected - auto-rotation may not be working', 'wpshadow' );
		}

		// Check for images with EXIF orientation data.
		global $wpdb;

		// Find JPEG images (most likely to have EXIF).
		$jpeg_images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = 'attachment'
				AND (p.post_mime_type = %s OR p.post_mime_type = %s)
				AND p.post_date > %s
				LIMIT 20",
				'image/jpeg',
				'image/jpg',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		$rotation_issues_count = 0;
		$upload_dir = wp_upload_dir();

		if ( function_exists( 'exif_read_data' ) ) {
			foreach ( $jpeg_images as $image ) {
				$file_path = $upload_dir['basedir'] . '/' . $image->file_path;
				
				if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
					continue;
				}

				// Read EXIF data.
				$exif = @exif_read_data( $file_path );
				
				if ( $exif && isset( $exif['Orientation'] ) ) {
					// Orientation values: 1 = normal, 3 = 180°, 6 = 90° CW, 8 = 90° CCW.
					if ( in_array( $exif['Orientation'], array( 3, 6, 8 ), true ) ) {
						// Check if WordPress metadata includes orientation correction.
						$metadata = wp_get_attachment_metadata( $image->ID );
						
						// If metadata doesn't show the rotation was applied, it's an issue.
						if ( empty( $metadata['image_meta']['orientation'] ) ) {
							$rotation_issues_count++;
						}
					}
				}
			}
		}

		if ( $rotation_issues_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image has EXIF rotation data that may not be applied',
					'%d recent images have EXIF rotation data that may not be applied',
					$rotation_issues_count,
					'wpshadow'
				),
				$rotation_issues_count
			);
		}

		// Check for WordPress core image rotation support (WP 5.3+).
		if ( ! function_exists( 'wp_image_maybe_exif_rotate' ) ) {
			$issues[] = __( 'WordPress version does not include wp_image_maybe_exif_rotate() - update to 5.3+ for automatic rotation', 'wpshadow' );
		}

		// Check memory limit for rotation operations.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$min_memory = 128 * 1024 * 1024; // 128MB minimum.
		
		if ( $memory_limit > 0 && $memory_limit < $min_memory ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit (%s) is low - image rotation may fail for large files', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for plugins that might interfere.
		$active_plugins = get_option( 'active_plugins', array() );
		$rotation_plugins = array(
			'enable-media-replace' => __( 'May affect EXIF orientation handling', 'wpshadow' ),
			'regenerate-thumbnails' => __( 'May regenerate without applying rotation', 'wpshadow' ),
		);

		foreach ( $rotation_plugins as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = sprintf(
						/* translators: %s: message */
						__( 'Plugin note: %s', 'wpshadow' ),
						$message
					);
					break;
				}
			}
		}

		// Check for disabled PHP functions.
		$disabled_functions = explode( ',', ini_get( 'disable_functions' ) );
		$disabled_functions = array_map( 'trim', $disabled_functions );
		
		$required_functions = array( 'exif_read_data', 'imagerotate' );
		foreach ( $required_functions as $func ) {
			if ( in_array( $func, $disabled_functions, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: function name */
					__( 'Required PHP function disabled: %s', 'wpshadow' ),
					$func
				);
			}
		}

		// Check for GD imagerotate quality parameter support.
		if ( $has_gd && function_exists( 'imagerotate' ) ) {
			// Test if imagerotate preserves quality.
			$test_img = @imagecreatetruecolor( 10, 10 );
			if ( $test_img ) {
				$rotated = @imagerotate( $test_img, 90, 0 );
				if ( false === $rotated ) {
					$issues[] = __( 'GD imagerotate() test failed - rotation may not work properly', 'wpshadow' );
				} else {
					@imagedestroy( $rotated );
				}
				@imagedestroy( $test_img );
			}
		}

		// Check for max_execution_time (rotation can be slow).
		$max_execution = (int) ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_execution_time (%d seconds) is low - image rotation may timeout', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for specific orientation values in metadata.
		$metadata_with_orientation = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attachment_metadata'
				AND meta_value LIKE %s",
				'%orientation%'
			)
		);

		if ( $metadata_with_orientation > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image has orientation metadata - verify rotation was applied correctly',
					'%d images have orientation metadata - verify rotation was applied correctly',
					$metadata_with_orientation,
					'wpshadow'
				),
				$metadata_with_orientation
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
						'%d issue detected with image rotation handling',
						'%d issues detected with image rotation handling',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-rotation-issues',
				'details'      => array(
					'issues'                 => $issues,
					'has_exif'               => $has_exif,
					'has_imagerotate'        => function_exists( 'imagerotate' ),
					'imagick_can_rotate'     => $imagick_can_rotate,
					'rotation_issues_count'  => $rotation_issues_count,
					'metadata_with_orientation' => $metadata_with_orientation,
				),
			);
		}

		return null;
	}
}
