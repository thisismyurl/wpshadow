<?php
/**
 * Wordpress Application Passwords Security Diagnostic
 *
 * Wordpress Application Passwords Security issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1251.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Application Passwords Security Diagnostic Class
 *
 * @since 1.1251.0000
 */
class Diagnostic_WordpressApplicationPasswordsSecurity extends Diagnostic_Base {

	protected static $slug = 'wordpress-application-passwords-security';
	protected static $title = 'Wordpress Application Passwords Security';
	protected static $description = 'Wordpress Application Passwords Security issue detected';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check 1: Verify application passwords enabled
		$app_passwords = get_option( 'app_passwords_enabled', true );
		if ( ! $app_passwords ) {
			$issues[] = __( 'Application passwords not enabled', 'wpshadow' );
		}

		// Check 2: Check SSL requirement
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for app passwords', 'wpshadow' );
		}

		// Check 3: Verify password entropy
		$password_length = get_option( 'app_password_length', 0 );
		if ( $password_length < 24 ) {
			$issues[] = __( 'Application password length not secure', 'wpshadow' );
		}

		// Check 4: Check revocation policy
		$revocation_policy = get_option( 'app_password_revocation', false );
		if ( ! $revocation_policy ) {
			$issues[] = __( 'Application password revocation policy not set', 'wpshadow' );
		}

		// Check 5: Verify audit logging
		$audit_logging = get_option( 'app_password_audit_logging', false );
		if ( ! $audit_logging ) {
			$issues[] = __( 'Application password usage logging not enabled', 'wpshadow' );
		}

		// Check 6: Check token expiration
		$token_expiry = get_option( 'app_password_expiration', 0 );
		if ( $token_expiry === 0 ) {
			$issues[] = __( 'Application password expiration not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 100, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WordPress application password security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-application-passwords-security',
			);
		}

		return null;
	}
}

	}
}
