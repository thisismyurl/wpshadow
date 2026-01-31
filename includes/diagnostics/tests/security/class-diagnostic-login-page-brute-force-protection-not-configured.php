<?php
/**
 * Login Page Brute Force Protection Not Configured Diagnostic
 *
 * Checks if brute force protection is configured.
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
 * Login Page Brute Force Protection Not Configured Diagnostic Class
 *
 * Detects missing brute force protection.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Login_Page_Brute_Force_Protection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-brute-force-protection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Brute Force Protection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if brute force protection is configured';

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
		// Check for brute force protection
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Login page brute force protection is not configured. Enable login attempt limits to prevent credential-based attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/login-page-brute-force-protection-not-configured',
			);
		}

		return null;
	}
}
