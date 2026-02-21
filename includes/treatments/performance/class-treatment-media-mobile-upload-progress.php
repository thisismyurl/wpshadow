<?php
/**
 * Mobile Upload Progress Indicators Treatment
 *
 * Detects if the media uploader provides progress feedback on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1635
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Mobile_Upload_Progress Class
 *
 * Tests if mobile upload progress indicators are properly implemented,
 * providing feedback on slow network connections during media uploads.
 *
 * @since 1.6033.1635
 */
class Treatment_Media_Mobile_Upload_Progress extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-upload-progress';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Progress Indicators';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media uploader provides progress feedback on mobile';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Mobile_Upload_Progress' );
	}
}
