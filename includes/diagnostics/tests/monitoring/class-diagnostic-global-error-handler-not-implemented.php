<?php
/**
 * Global Error Handler Not Implemented Diagnostic
 *
 * Checks if global error handler is implemented.
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
 * Global Error Handler Not Implemented Diagnostic Class
 *
 * Detects missing global error handler.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Global_Error_Handler_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'global-error-handler-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Global Error Handler Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if global error handler is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for error handler registration
		if ( ! has_action( 'shutdown', 'handle_fatal_errors' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Global error handler is not implemented. Register set_error_handler and register_shutdown_function to catch and log all errors including fatal ones.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/global-error-handler-not-implemented',
			);
		}

		return null;
	}
}
