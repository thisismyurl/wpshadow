<?php
/**
 * Login URL Not Changed From Default Diagnostic
 *
 * Checks if login URL has been changed.
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
 * Login URL Not Changed From Default Diagnostic Class
 *
 * Detects default login URL.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Login_URL_Not_Changed_From_Default extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-url-not-changed-from-default';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login URL Not Changed From Default';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login URL has been changed';

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
		// Check if login URL has been customized
		if ( ! has_filter( 'login_url', 'customize_login_url' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Login URL is still the default wp-login.php. Change it to a custom URL to reduce brute force attacks on your login page.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/login-url-not-changed-from-default',
			);
		}

		return null;
	}
}
