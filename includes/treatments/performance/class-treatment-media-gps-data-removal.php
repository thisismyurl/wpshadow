<?php
/**
 * Media GPS Data Removal Treatment
 *
 * Verifies GPS/location data is removed from images
 * for privacy and security.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_GPS_Data_Removal Class
 *
 * Checks for GPS data in EXIF metadata on recent uploads.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_GPS_Data_Removal extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-gps-data-removal';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'GPS Data Removal';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GPS/location data is removed from images';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_GPS_Data_Removal' );
	}
}
