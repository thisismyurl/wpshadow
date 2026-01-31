<?php
/**
 * User Session Cookie Security Not Configured Diagnostic
 *
 * Checks if session cookies are secure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Session Cookie Security Not Configured Diagnostic Class
 *
 * Detects insecure session cookies.
 *
 * @since 1.2601.2315
 */
class Diagnostic_User_Session_Cookie_Security_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-session-cookie-security-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Session Cookie Security Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if session cookies are secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if HTTPS is enforced
		if ( is_ssl() ) {
			// Check SECURE_AUTH_COOKIE setting
			if ( ! defined( 'SECURE_AUTH_COOKIE' ) || ! SECURE_AUTH_COOKIE ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Session cookies are not marked as secure. Add SECURE_AUTH_COOKIE to wp-config.php to protect login sessions.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 60,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/user-session-cookie-security-not-configured',
				);
			}
		}

		return null;
	}
}
