<?php
/**
 * Upload Timeout Errors Treatment
 *
 * Monitors for upload timeouts during large file uploads. Tests max_execution_time
 * and max_input_time settings.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Timeout Errors Treatment Class
 *
 * Checks for timeout issues during file uploads.
 *
 * @since 0.6093.1200
 */
class Treatment_Upload_Timeout_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-timeout-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Timeout Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates max_execution_time and max_input_time for file uploads';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Upload_Timeout_Errors' );
	}
}
