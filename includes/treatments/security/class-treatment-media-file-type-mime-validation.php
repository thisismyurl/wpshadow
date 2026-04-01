<?php
/**
 * Media File Type MIME Validation Treatment
 *
 * Validates MIME type checking for uploaded files and tests
 * for MIME spoofing vulnerabilities.
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
 * Treatment_Media_File_Type_MIME_Validation Class
 *
 * Validates that WordPress properly checks and validates MIME types
 * for uploaded files. Detects potential MIME spoofing vulnerabilities
 * where the actual file content doesn't match the declared MIME type.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_File_Type_MIME_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-type-mime-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File Type MIME Validation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates MIME type checking and detects spoofing vulnerabilities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_File_Type_MIME_Validation' );
	}
}
