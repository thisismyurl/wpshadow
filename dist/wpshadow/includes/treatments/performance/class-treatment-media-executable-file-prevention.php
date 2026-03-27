<?php
/**
 * Media Executable File Prevention Treatment
 *
 * Validates prevention of executable file uploads by
 * checking allowed MIME types and upload filters.
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
 * Treatment_Media_Executable_File_Prevention Class
 *
 * Ensures executable file types are not allowed for upload.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Executable_File_Prevention extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-executable-file-prevention';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Executable File Prevention';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates prevention of executable file uploads';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Executable_File_Prevention' );
	}
}
