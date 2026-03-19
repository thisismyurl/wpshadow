<?php
/**
 * Media Image Rotation Issues Treatment
 *
 * Tests EXIF orientation handling to detect images displaying
 * sideways or upside-down due to rotation metadata.
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
 * Treatment_Media_Image_Rotation_Issues Class
 *
 * Checks if WordPress properly handles EXIF orientation data from
 * images taken with mobile devices and cameras that may be rotated.
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Image_Rotation_Issues' );
	}
}
