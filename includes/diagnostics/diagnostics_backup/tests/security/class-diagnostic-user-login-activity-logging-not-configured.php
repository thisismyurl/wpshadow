<?php
/**
 * User Login Activity Logging Not Configured Diagnostic
 *
 * Checks if login activity is being logged.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Activity Logging Not Configured Diagnostic Class
 *
 * Detects missing login activity logging.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Login_Activity_Logging_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-login-activity-logging-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Login Activity Logging Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login activity is logged';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for activity logging plugins
		$logging_plugins = array(
			'stream/stream.php',
			'simple-history/simple-history.php',
			'wpscan-activity-log/wpscan-activity-log.php',
			'wordfence/wordfence.php',
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
				'description'   => __( 'User login activity is not being logged. Without activity logs, you cannot detect unauthorized access or security incidents.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-login-activity-logging-not-configured',
			);
		}

		return null;
	}
}
