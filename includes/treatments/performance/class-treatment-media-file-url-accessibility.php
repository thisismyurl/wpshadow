<?php
/**
 * Media File URL Accessibility Treatment
 *
 * Tests whether uploaded media files are accessible via
 * their public URLs and detects 404 errors.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_File_URL_Accessibility Class
 *
 * Verifies that recent media attachments are accessible
 * via their URLs without returning errors.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_File_URL_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-url-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File URL Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media URLs for accessibility and 404 errors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_File_URL_Accessibility' );
	}
}
