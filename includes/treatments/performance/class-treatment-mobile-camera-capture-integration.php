<?php
/**
 * Mobile Camera Capture Integration Treatment
 *
 * Tests direct camera capture from mobile devices in media uploader.
 * Validates camera API access and mobile upload functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Camera Capture Integration Treatment Class
 *
 * Checks if mobile camera capture is properly integrated in the
 * WordPress media uploader for improved mobile user experience.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Camera_Capture_Integration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-camera-capture-integration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Camera Capture Integration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests direct camera capture from mobile devices in media uploader';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if the media library properly supports HTML5 camera capture
	 * attributes for mobile devices.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Camera_Capture_Integration' );
	}
}
