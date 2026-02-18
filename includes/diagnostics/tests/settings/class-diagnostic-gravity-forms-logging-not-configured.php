<?php
/**
 * Gravity Forms Logging Not Configured Diagnostic
 *
 * Checks if Gravity Forms logging is configured.
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
 * Gravity Forms Logging Not Configured Diagnostic Class
 *
 * Detects missing Gravity Forms logging.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Gravity_Forms_Logging_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravity-forms-logging-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gravity Forms Logging Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Gravity Forms logging is configured';

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
		// Check if Gravity Forms is active
		if ( ! class_exists( 'GFCommon' ) ) {
			return null;
		}

		// Check if logging is enabled
		if ( ! get_option( 'gf_logging_enabled' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gravity Forms logging is not configured. Enable Gravity Forms logging to debug form submission issues and track user interactions.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gravity-forms-logging-not-configured',
			);
		}

		return null;
	}
}
