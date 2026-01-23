<?php
declare(strict_types=1);
/**
 * JWT Secret Key Strength Diagnostic
 *
 * Philosophy: Cryptography security - strong JWT secrets
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check JWT secret key strength.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_JWT_Secret_Strength extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if JWT plugin is active
		$jwt_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
			'jwt-auth/jwt-auth.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_jwt = false;
		
		foreach ( $jwt_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_jwt = true;
				break;
			}
		}
		
		if ( ! $has_jwt ) {
			return null; // No JWT
		}
		
		// Check JWT_AUTH_SECRET_KEY constant
		if ( ! defined( 'JWT_AUTH_SECRET_KEY' ) ) {
			return array(
				'id'          => 'jwt-secret-strength',
				'title'       => 'JWT Secret Key Not Defined',
				'description' => 'JWT authentication is active but JWT_AUTH_SECRET_KEY is not defined in wp-config.php. Without a secret key, JWT tokens cannot be validated securely. Define a strong secret key immediately.',
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-jwt-secret/',
				'training_link' => 'https://wpshadow.com/training/jwt-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		$secret = JWT_AUTH_SECRET_KEY;
		$secret_length = strlen( $secret );
		
		// Check secret strength
		if ( $secret_length < 32 ) {
			return array(
				'id'          => 'jwt-secret-strength',
				'title'       => 'Weak JWT Secret Key',
				'description' => sprintf(
					'JWT_AUTH_SECRET_KEY is only %d characters. Weak secrets allow token forgery, letting attackers impersonate any user. Use a cryptographically random secret of 64+ characters.',
					$secret_length
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/strengthen-jwt-secret/',
				'training_link' => 'https://wpshadow.com/training/jwt-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}

}