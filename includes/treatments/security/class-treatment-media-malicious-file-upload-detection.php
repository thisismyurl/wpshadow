<?php
/**
 * Media Malicious File Upload Detection Treatment
 *
 * Tests for malicious file upload attempts. Validates file type
 * verification beyond extension to catch spoofing attacks.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.2099
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Malicious_File_Upload_Detection Class
 *
 * Checks if WordPress has proper file upload validation to detect
 * and reject malicious file uploads. Tests for:
 * - File type verification beyond extension
 * - MIME type checking
 * - Executable file rejection
 *
 * @since 1.6033.2099
 */
class Treatment_Media_Malicious_File_Upload_Detection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-malicious-file-upload-detection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Malicious File Upload Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates file type verification beyond extension to catch spoofing';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2099
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Malicious_File_Upload_Detection' );
	}
}
