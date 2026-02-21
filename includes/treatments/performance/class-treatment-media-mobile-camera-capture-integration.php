<?php
/**
 * Media Mobile Camera Capture Integration Treatment
 *
 * Tests direct camera capture support in the media uploader
 * for mobile devices.
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
 * Treatment_Media_Mobile_Camera_Capture_Integration Class
 *
 * Checks media uploader configuration for mobile capture support.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Mobile_Camera_Capture_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-camera-capture-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Camera Capture Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests mobile camera capture support in media uploader';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Mobile_Camera_Capture_Integration' );
	}
}
