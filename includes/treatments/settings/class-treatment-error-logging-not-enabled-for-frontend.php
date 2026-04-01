<?php
/**
 * Error Logging Not Enabled For Frontend Treatment
 *
 * Checks if frontend error logging is enabled.
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
 * Error Logging Not Enabled For Frontend Treatment Class
 *
 * Detects missing frontend error logging.
 *
 * @since 0.6093.1200
 */
class Treatment_Error_Logging_Not_Enabled_For_Frontend extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging-not-enabled-for-frontend';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Error Logging Not Enabled For Frontend';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if frontend error logging is enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Error_Logging_Not_Enabled_For_Frontend' );
	}
}
