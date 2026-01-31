<?php
/**
 * User Session Timeout Not Configured Diagnostic
 *
 * Checks if user session timeout is configured.
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
 * User Session Timeout Not Configured Diagnostic Class
 *
 * Detects missing session timeout configuration.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Session_Timeout_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-session-timeout-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Session Timeout Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user session timeout is set';

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
		// Check for session timeout configuration
		if ( ! defined( 'AUTH_COOKIE_EXPIRATION' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User session timeout is not configured. Long session timeouts increase security risk if a user forgets to log out.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-session-timeout-not-configured',
			);
		}

		$timeout = constant( 'AUTH_COOKIE_EXPIRATION' );
		if ( $timeout > 604800 ) { // 7 days in seconds
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'User session timeout is %d days. Consider reducing to 1-3 days for better security.', 'wpshadow' ),
					(int) ( $timeout / 86400 )
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-session-timeout-not-configured',
			);
		}

		return null;
	}
}
