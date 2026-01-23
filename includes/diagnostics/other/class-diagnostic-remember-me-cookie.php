<?php
declare(strict_types=1);
/**
 * Remember Me Cookie Security Diagnostic
 *
 * Philosophy: Session security - secure auth cookie configuration
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check Remember Me cookie security settings.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Remember_Me_Cookie extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check if HTTPS is enforced for auth cookies
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			if ( is_ssl() ) {
				$issues[] = 'FORCE_SSL_ADMIN not defined (cookies may be sent over HTTP)';
			}
		}

		// Check cookie security constants
		if ( defined( 'COOKIEHASH' ) ) {
			$hash_length = strlen( COOKIEHASH );
			if ( $hash_length < 32 ) {
				$issues[] = sprintf( 'Weak COOKIEHASH (%d chars, should be 32+)', $hash_length );
			}
		}

		// Check Remember Me duration
		$remember_me_duration = apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, 0, true );
		if ( $remember_me_duration > ( 30 * DAY_IN_SECONDS ) ) {
			$issues[] = sprintf( 'Remember Me lasts %d days (recommend 14-30 days max)', $remember_me_duration / DAY_IN_SECONDS );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => 'remember-me-cookie',
				'title'         => 'Remember Me Cookie Security Issues',
				'description'   => sprintf(
					'Authentication cookie configuration issues detected: %s. These weaken session security and enable session hijacking.',
					implode( '; ', $issues )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/secure-auth-cookies/',
				'training_link' => 'https://wpshadow.com/training/cookie-security/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}

}