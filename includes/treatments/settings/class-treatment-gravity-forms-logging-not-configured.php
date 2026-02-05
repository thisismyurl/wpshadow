<?php
/**
 * Gravity Forms Logging Not Configured Treatment
 *
 * Checks if Gravity Forms logging is configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Logging Not Configured Treatment Class
 *
 * Detects missing Gravity Forms logging.
 *
 * @since 1.6030.2352
 */
class Treatment_Gravity_Forms_Logging_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravity-forms-logging-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Gravity Forms Logging Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Gravity Forms logging is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
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
