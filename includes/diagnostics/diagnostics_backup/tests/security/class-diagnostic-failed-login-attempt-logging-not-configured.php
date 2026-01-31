<?php
/**
 * Failed Login Attempt Logging Not Configured Diagnostic
 *
 * Checks if failed login attempts are logged.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Failed Login Attempt Logging Not Configured Diagnostic Class
 *
 * Detects missing failed login logging.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Failed_Login_Attempt_Logging_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'failed-login-attempt-logging-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Failed Login Attempt Logging Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if failed login attempts are logged';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security plugins that log failed attempts
		$security_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'jetpack/jetpack.php',
		);

		$logging_active = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$logging_active = true;
				break;
			}
		}

		if ( ! $logging_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Failed login attempts are not logged. Track failed logins to detect brute force attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/failed-login-attempt-logging-not-configured',
			);
		}

		return null;
	}
}
