<?php
/**
 * GPS Data Removal Diagnostic
 *
 * Verifies GPS/location data is removed from images for privacy.
 * Tests EXIF stripping specifically for geolocation metadata.
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
 * GPS Data Removal Diagnostic Class
 *
 * Checks if GPS/location metadata is present in uploaded images,
 * which poses significant privacy risks if not stripped.
 *
 * @since 1.6093.1200
 */
class Diagnostic_GPS_Data_Removal extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gps-data-removal';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GPS Data Removal';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GPS/location data is removed from images for privacy';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans recent image uploads for GPS/location EXIF data that could
	 * reveal user location and compromise privacy.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if EXIF functions are available.
		if ( ! function_exists( 'exif_read_data' ) ) {
			return null; // Cannot test without EXIF support.
		}

		// Get recent image attachments.
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$attachments = get_posts( $args );

		if ( empty( $attachments ) ) {
			return null; // No images to test.
		}

		$images_with_gps    = array();
		$images_tested      = 0;
		$total_images_found = 0;

		foreach ( $attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );

			if ( ! $file_path || ! file_exists( $file_path ) ) {
				continue;
			}

			$images_tested++;

			// Read EXIF data.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- EXIF data may not exist.
			$exif_data = @exif_read_data( $file_path );

			if ( empty( $exif_data ) ) {
				continue;
			}

			// Check specifically for GPS data.
			$gps_fields = array(
				'GPSLatitude',
				'GPSLatitudeRef',
				'GPSLongitude',
				'GPSLongitudeRef',
				'GPSAltitude',
				'GPSAltitudeRef',
				'GPSTimeStamp',
				'GPSDateStamp',
			);

			$has_gps        = false;
			$gps_data_found = array();

			foreach ( $gps_fields as $field ) {
				if ( isset( $exif_data[ $field ] ) ) {
					$has_gps            = true;
					$gps_data_found[]   = $field;
				}
			}

			if ( $has_gps ) {
				$total_images_found++;

				$images_with_gps[] = array(
					'file'           => basename( $file_path ),
					'attachment_id'  => $attachment->ID,
					'upload_date'    => get_the_date( 'Y-m-d H:i:s', $attachment->ID ),
					'gps_fields'     => $gps_data_found,
					'user_id'        => $attachment->post_author,
				);

				// Limit detailed results to 5 samples.
				if ( 5 <= count( $images_with_gps ) ) {
					break;
				}
			}
		}

		// If GPS data found, this is a privacy concern.
		if ( 0 < $total_images_found ) {
			$severity     = 1 === $total_images_found ? 'medium' : 'high';
			$threat_level = 1 === $total_images_found ? 65 : 75;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of images with GPS data, 2: total images tested */
					__( '%1$d of %2$d recent images contain GPS location data that could reveal user locations', 'wpshadow' ),
					$total_images_found,
					$images_tested
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gps-data-removal',
				'details'      => array(
					'images_tested'       => $images_tested,
					'images_with_gps'     => $total_images_found,
					'sample_images'       => $images_with_gps,
					'privacy_risk'        => __( 'GPS data can reveal exact locations where photos were taken, including home addresses', 'wpshadow' ),
					'recommendation'      => __( 'Install a plugin to automatically strip GPS/EXIF data on upload, or manually remove GPS data before uploading', 'wpshadow' ),
					'suggested_plugins'   => array(
						'EWWW Image Optimizer',
						'ShortPixel',
						'Imagify',
					),
				),
			);
		}

		return null;
	}
}
