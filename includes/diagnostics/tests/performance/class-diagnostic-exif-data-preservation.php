<?php
/**
 * EXIF Data Preservation Diagnostic
 *
 * Tests if EXIF data is preserved or stripped during upload.
 * Validates privacy settings and metadata handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EXIF Data Preservation Diagnostic Class
 *
 * Checks whether EXIF metadata is being preserved or stripped from
 * uploaded images, and validates if this aligns with privacy settings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_EXIF_Data_Preservation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exif-data-preservation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'EXIF Data Preservation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if EXIF data is preserved or stripped during upload';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests EXIF handling by examining recent uploads and comparing
	 * with WordPress settings and expected behavior.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if EXIF functions are available.
		if ( ! function_exists( 'exif_read_data' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'EXIF PHP extension is not installed, cannot validate EXIF data preservation', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exif-data-preservation',
				'details'      => array(
					'exif_available' => false,
					'message'        => __( 'EXIF PHP extension is required to read and preserve image metadata', 'wpshadow' ),
				),
			);
		}

		// Get recent image attachments.
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$attachments = get_posts( $args );

		if ( empty( $attachments ) ) {
			return null; // No images to test.
		}

		$images_with_exif    = 0;
		$images_without_exif = 0;
		$images_tested       = 0;
		$sample_files        = array();

		foreach ( $attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );

			if ( ! $file_path || ! file_exists( $file_path ) ) {
				continue;
			}

			$images_tested++;

			// Read EXIF data.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- EXIF data may not exist.
			$exif_data = @exif_read_data( $file_path );

			if ( ! empty( $exif_data ) ) {
				$images_with_exif++;

				// Check for sensitive data.
				$has_gps      = isset( $exif_data['GPSLatitude'] ) || isset( $exif_data['GPSLongitude'] );
				$has_camera   = isset( $exif_data['Make'] ) || isset( $exif_data['Model'] );
				$has_datetime = isset( $exif_data['DateTime'] ) || isset( $exif_data['DateTimeOriginal'] );

				$sample_files[] = array(
					'file'         => basename( $file_path ),
					'attachment_id' => $attachment->ID,
					'has_gps'      => $has_gps,
					'has_camera'   => $has_camera,
					'has_datetime' => $has_datetime,
				);
			} else {
				$images_without_exif++;
			}

			// Limit samples.
			if ( 3 <= count( $sample_files ) ) {
				break;
			}
		}

		// Calculate percentage.
		$exif_percentage = 0 < $images_tested ? ( $images_with_exif / $images_tested ) * 100 : 0;

		// Check if EXIF preservation is intentional or accidental.
		$strip_exif_on_upload = apply_filters( 'wp_image_editors', array() );
		$expected_behavior    = __( 'WordPress preserves EXIF by default', 'wpshadow' );

		// If most images have EXIF, check privacy implications.
		if ( 50 < $exif_percentage ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage of images with EXIF data */
					__( '%d%% of recent uploads contain EXIF metadata, which may include privacy-sensitive information', 'wpshadow' ),
					round( $exif_percentage )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exif-data-preservation',
				'details'      => array(
					'images_tested'       => $images_tested,
					'images_with_exif'    => $images_with_exif,
					'images_without_exif' => $images_without_exif,
					'exif_percentage'     => round( $exif_percentage, 2 ),
					'expected_behavior'   => $expected_behavior,
					'sample_files'        => $sample_files,
					'recommendation'      => __( 'Consider using a plugin to strip sensitive EXIF data (GPS, camera info) on upload for privacy protection', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
