<?php
/**
 * Mobile Upload Compatibility Treatment
 *
 * Tests file uploads from mobile devices. Verifies camera/gallery access.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Mobile_Upload_Compatibility Class
 *
 * Validates mobile device upload compatibility. Mobile browsers have specific
 * requirements for file uploads, especially camera/gallery access via HTML5
 * input accept attributes and capture capabilities.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Upload_Compatibility extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-upload-compatibility';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Compatibility';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests file uploads from mobile devices';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Mobile-friendly upload interface
	 * - File input accept attributes
	 * - Plupload mobile runtime (HTML5)
	 * - Mobile-specific upload errors
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Upload_Compatibility' );
	}
}
