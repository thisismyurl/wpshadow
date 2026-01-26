<?php
/**
 * Diagnostic: HttpOnly Cookie Flag
 *
 * Checks if WordPress authentication cookies have HttpOnly flag set.
 * HttpOnly prevents JavaScript access to cookies, mitigating XSS attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Httponly_Cookie_Flag
 *
 * Validates HttpOnly flag on WordPress cookies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Httponly_Cookie_Flag extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'httponly-cookie-flag';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HttpOnly Cookie Flag';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if authentication cookies have HttpOnly flag';

	/**
	 * Check HttpOnly cookie flag status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if COOKIEHASH is defined (required for cookie names).
		if ( ! defined( 'COOKIEHASH' ) ) {
			return null; // Can't check without cookie hash.
		}

		// Get WordPress cookie names.
		$auth_cookie     = 'wordpress_' . COOKIEHASH;
		$logged_in_cookie = 'wordpress_logged_in_' . COOKIEHASH;

		// Check PHP session cookie settings.
		$session_httponly = (bool) ini_get( 'session.cookie_httponly' );

		// Check if WordPress sets httponly on cookies via filter.
		$wp_httponly = apply_filters( 'wp_auth_cookie_httponly', true );

		if ( ! $wp_httponly ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress authentication cookies do not have HttpOnly flag enabled. This increases XSS vulnerability.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/httponly_cookie_flag',
				'meta'        => array(
					'wp_httponly'      => false,
					'session_httponly' => $session_httponly,
				),
			);
		}

		// Informational: Check if PHP session cookies also have httponly.
		if ( ! $session_httponly ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP session cookies do not have HttpOnly flag. Consider enabling session.cookie_httponly in php.ini.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/httponly_cookie_flag',
				'meta'        => array(
					'wp_httponly'      => true,
					'session_httponly' => false,
				),
			);
		}

		// HttpOnly is properly configured.
		return null;
	}
}
