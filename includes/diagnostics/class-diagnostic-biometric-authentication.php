<?php declare(strict_types=1);
/**
 * Biometric Authentication Support Diagnostic
 *
 * Philosophy: Modern authentication - fingerprint/face recognition
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if biometric authentication is available.
 */
class Diagnostic_Biometric_Authentication {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$biometric_plugins = array(
			'biometric-login/biometric-login.php',
			'webauthn/webauthn.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $biometric_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'biometric-authentication',
			'title'       => 'No Biometric Authentication Support',
			'description' => 'Biometric/WebAuthn authentication not available. Offer passwordless login options (fingerprint, face recognition) for improved security and UX.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/add-biometric-auth/',
			'training_link' => 'https://wpshadow.com/training/webauthn/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
