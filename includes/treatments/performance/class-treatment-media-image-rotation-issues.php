<?php
/**
 * Media Image Rotation Issues Treatment
 *
 * Tests EXIF orientation handling to detect images displaying
 * sideways or upside-down due to rotation metadata.
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
 * Treatment_Media_Image_Rotation_Issues Class
 *
 * Checks if WordPress properly handles EXIF orientation data from
 * images taken with mobile devices and cameras that may be rotated.
 *
 * @since 1.6033.1545
 */
class Treatment_Media_Image_Rotation_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-rotation-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Rotation Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests EXIF orientation handling for rotated images';

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

		// Check if EXIF extension is available.
		if ( ! extension_loaded( 'exif' ) ) {
			$issues[] = __( 'PHP EXIF extension not loaded; image rotation metadata cannot be read', 'wpshadow' );
		}

		// Check if WordPress has image rotation support.
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			$issues[] = __( 'wp_read_image_metadata function not available; EXIF data cannot be processed', 'wpshadow' );
		}

		// Check if image editor supports rotation.
		if ( ! class_exists( 'WP_Image_Editor' ) ) {
			$issues[] = __( 'WP_Image_Editor class not found; automatic rotation correction not available', 'wpshadow' );
		}

		// Check if auto-rotation is enabled.
		$auto_rotate = apply_filters( 'wp_image_maybe_exif_rotate', true );
		if ( ! $auto_rotate ) {
			$issues[] = __( 'Automatic EXIF rotation is disabled; images may display incorrectly', 'wpshadow' );
		}

		// Check for GD or Imagick support (needed for rotation).
		$has_gd = extension_loaded( 'gd' ) && function_exists( 'imagecreatefromjpeg' );
		$has_imagick = extension_loaded( 'imagick' );
		
		if ( ! $has_gd && ! $has_imagick ) {
			$issues[] = __( 'Neither GD nor ImageMagick extension available; images cannot be rotated', 'wpshadow' );
		}

		// Sample recent uploads to check for rotation issues.
		$recent_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$rotation_issues_found = 0;
		foreach ( $recent_images as $image ) {
			$file_path = get_attached_file( $image->ID );
			
			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			// Check if image has EXIF orientation data.
			if ( extension_loaded( 'exif' ) && function_exists( 'exif_read_data' ) ) {
				$exif = @exif_read_data( $file_path );
				
				if ( $exif && isset( $exif['Orientation'] ) ) {
					$orientation = $exif['Orientation'];
					
					// Orientation values 3, 6, 8 require rotation.
					if ( in_array( $orientation, array( 3, 6, 8 ), true ) ) {
						// Check if WordPress metadata indicates rotation was applied.
						$meta = wp_get_attachment_metadata( $image->ID );
						
						if ( ! isset( $meta['image_meta']['orientation'] ) || $meta['image_meta']['orientation'] !== $orientation ) {
							$rotation_issues_found++;
						}
					}
				}
			}
		}

		if ( $rotation_issues_found > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image with EXIF rotation data may not have been auto-rotated',
					'%d recent images with EXIF rotation data may not have been auto-rotated',
					$rotation_issues_found,
					'wpshadow'
				),
				$rotation_issues_found
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => implode( '. ', $issues ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-image-rotation-issues',
			);
		}

		return null;
	}
}
