<?php
/**
 * Activity Logging Not Enabled For User Actions Diagnostic
 *
 * Checks if activity logging is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity Logging Not Enabled For User Actions Diagnostic Class
 *
 * Detects disabled activity logging.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Activity_Logging_Not_Enabled_For_User_Actions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'activity-logging-not-enabled-for-user-actions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Activity Logging Not Enabled For User Actions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if activity logging is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if activity logging is enabled
		if ( ! is_plugin_active( 'stream/stream.php' ) && ! get_option( 'enable_activity_log' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Activity logging is not enabled for user actions. Enable activity logging to track who did what and when for security audits.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/activity-logging-not-enabled-for-user-actions',
			);
		}

		return null;
	}
}
