<?php
/**
 * Media GPS Data Removal Diagnostic
 *
 * Verifies GPS/location data is removed from images
 * for privacy and security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_GPS_Data_Removal Class
 *
 * Checks for GPS data in EXIF metadata on recent uploads.
 *
 * @since 1.6033.1625
 */
class Diagnostic_Media_GPS_Data_Removal extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-gps-data-removal';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GPS Data Removal';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GPS/location data is removed from images';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! extension_loaded( 'exif' ) ) {
			$issues[] = __( 'PHP EXIF extension is not enabled; GPS metadata cannot be detected or removed', 'wpshadow' );
		}

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$gps_found = 0;
		foreach ( $attachments as $attachment ) {
			$file = get_attached_file( $attachment->ID );
			if ( empty( $file ) || ! file_exists( $file ) ) {
				continue;
			}

			$metadata = wp_read_image_metadata( $file );
			if ( ! empty( $metadata['latitude'] ) || ! empty( $metadata['longitude'] ) ) {
				$gps_found++;
			}
		}

		if ( $gps_found > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image contains GPS data; consider stripping location metadata for privacy',
					'%d recent images contain GPS data; consider stripping location metadata for privacy',
					$gps_found,
					'wpshadow'
				),
				$gps_found
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-gps-data-removal',
			);
		}

		return null;
	}
}
