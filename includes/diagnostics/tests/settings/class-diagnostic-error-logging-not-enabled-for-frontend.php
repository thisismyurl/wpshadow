<?php
/**
 * Error Logging Not Enabled For Frontend Diagnostic
 *
 * Checks if frontend error logging is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error Logging Not Enabled For Frontend Diagnostic Class
 *
 * Detects missing frontend error logging.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Error_Logging_Not_Enabled_For_Frontend extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging-not-enabled-for-frontend';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Logging Not Enabled For Frontend';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if frontend error logging is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for frontend error logging
		if ( ! get_option( 'enable_frontend_error_logging' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Frontend error logging is not enabled. Enable JavaScript error logging to catch and debug client-side errors.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/error-logging-not-enabled-for-frontend',
			);
		}

		return null;
	}
}
