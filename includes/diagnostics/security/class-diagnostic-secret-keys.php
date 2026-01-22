<?php
declare(strict_types=1);
/**
 * Secret Keys Security Diagnostic
 *
 * Philosophy: Security critical - detect default/weak salts
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for default or weak secret keys.
 */
class Diagnostic_Secret_Keys extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if using placeholder keys
		$keys = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY' );
		
		foreach ( $keys as $key ) {
			if ( defined( $key ) ) {
				$value = constant( $key );
				// Check for placeholder text
				if ( strpos( $value, 'put your unique phrase here' ) !== false || strlen( $value ) < 20 ) {
					return array(
						'id'          => 'secret-keys',
						'title'       => 'Default Secret Keys Detected',
						'description' => 'Your site is using default or weak secret keys/salts. Generate unique keys immediately to prevent session hijacking.',
						'severity'    => 'critical',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/regenerate-secret-keys/',
						'training_link' => 'https://wpshadow.com/training/secret-keys/',
						'auto_fixable' => false,
						'threat_level' => 90,
					);
				}
			}
		}
		
		return null;
	}
}
