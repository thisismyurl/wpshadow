<?php
declare(strict_types=1);
/**
 * SSL Client Certificate Authentication Diagnostic
 *
 * Philosophy: Network hardening - mutual TLS authentication
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if SSL client certificate authentication is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SSL_Client_Certificate extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_client_cert = get_option( 'wpshadow_ssl_client_cert_required' );
		
		if ( empty( $has_client_cert ) ) {
			return array(
				'id'          => 'ssl-client-certificate',
				'title'       => 'No SSL Client Certificate Authentication',
				'description' => 'Server does not require client SSL certificates. For sensitive installations, implement mutual TLS (client certificate authentication) for additional layer.',
				'severity'    => 'low',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-ssl-client-certificates/',
				'training_link' => 'https://wpshadow.com/training/mutual-tls/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
