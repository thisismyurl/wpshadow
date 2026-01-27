<?php
/**
 * Diagnostic: Session Cookie Security
 *
 * Checks PHP session cookie settings for security (httpOnly, secure, SameSite).
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
 * Class Diagnostic_Session_Cookie_Security
 *
 * Tests PHP session cookie security settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Session_Cookie_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'session-cookie-security';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Session Cookie Security';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks PHP session cookie security settings';

	/**
	 * Check session cookie security.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$httponly  = ini_get( 'session.cookie_httponly' );
		$secure    = ini_get( 'session.cookie_secure' );
		$samesite  = ini_get( 'session.cookie_samesite' );

		$issues = array();

		if ( empty( $httponly ) || '0' === $httponly ) {
			$issues[] = 'httpOnly not enabled (vulnerable to XSS)';
		}

		if ( is_ssl() && ( empty( $secure ) || '0' === $secure ) ) {
			$issues[] = 'Secure flag not enabled (cookies transmitted over HTTP)';
		}

		if ( empty( $samesite ) ) {
			$issues[] = 'SameSite attribute not set (vulnerable to CSRF)';
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of cookie security issues */
					__( 'Session cookie security issues detected: %s. Configure session.cookie_httponly, session.cookie_secure, and session.cookie_samesite in php.ini.', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/session_cookie_security',
				'meta'        => array(
					'httponly' => $httponly,
					'secure'   => $secure,
					'samesite' => $samesite,
					'issues'   => $issues,
				),
			);
		}

		return null;
	}
}
