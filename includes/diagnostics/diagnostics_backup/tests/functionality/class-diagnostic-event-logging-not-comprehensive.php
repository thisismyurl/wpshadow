<?php
/**
 * Event Logging Not Comprehensive Diagnostic
 *
 * Checks if comprehensive event logging is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Logging Not Comprehensive Diagnostic Class
 *
 * Detects missing comprehensive event logging.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Event_Logging_Not_Comprehensive extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'event-logging-not-comprehensive';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Event Logging Not Comprehensive';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comprehensive event logging is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for event logging plugins
		$logging_plugins = array(
			'stream/stream.php',
			'activity-log/activity-log.php',
		);

		$logging_active = false;
		foreach ( $logging_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$logging_active = true;
				break;
			}
		}

		if ( ! $logging_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comprehensive event logging is not configured. Enable activity logging to track all changes for security and troubleshooting.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/event-logging-not-comprehensive',
			);
		}

		return null;
	}
}
