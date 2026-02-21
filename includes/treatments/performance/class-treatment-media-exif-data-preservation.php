<?php
/**
 * Media EXIF Data Preservation Treatment
 *
 * Tests whether EXIF data is preserved during upload
 * and detects missing metadata handling.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_EXIF_Data_Preservation Class
 *
 * Checks for EXIF metadata availability on recent uploads.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_EXIF_Data_Preservation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-exif-data-preservation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'EXIF Data Preservation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if EXIF data is preserved during upload';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_EXIF_Data_Preservation' );
	}
}
