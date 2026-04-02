<?php
/**
 * Error Logging Treatment
 *
 * Checks if proper error logging is configured for debugging.
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
 * Error Logging Treatment Class
 *
 * Verifies that proper error logging is configured for debugging
 * and monitoring production issues.
 *
 * @since 1.6093.1200
 */
class Treatment_Error_Logging extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Error Logging Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if proper error logging is configured for debugging';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the error logging treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if logging issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\WordPress_Health\Diagnostic_Error_Logging' );
	}
}
