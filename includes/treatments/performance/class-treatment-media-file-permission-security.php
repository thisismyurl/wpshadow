<?php
/**
 * Media File Permission Security Treatment
 *
 * Validates file and directory permissions for uploads
 * to prevent overly permissive access (e.g., 777).
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_File_Permission_Security Class
 *
 * Checks for insecure permissions in the uploads directory
 * and recent media files.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_File_Permission_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-permission-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates secure file permissions in uploads directory';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_File_Permission_Security' );
	}
}
