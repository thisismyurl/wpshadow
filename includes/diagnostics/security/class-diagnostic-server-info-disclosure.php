<?php
declare(strict_types=1);
/**
 * Server Information Disclosure Diagnostic
 *
 * Philosophy: Information security - hide server details
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if server reveals version information.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Server_Info_Disclosure extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$response = wp_remote_head( home_url(), array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		$disclosed_headers = array();
		
		// Check for revealing headers
		if ( ! empty( $headers['server'] ) && $headers['server'] !== 'nginx' && $headers['server'] !== 'Apache' ) {
			$disclosed_headers[] = 'Server: ' . $headers['server'];
		}
		
		if ( ! empty( $headers['x-powered-by'] ) ) {
			$disclosed_headers[] = 'X-Powered-By: ' . $headers['x-powered-by'];
		}
		
		if ( ! empty( $disclosed_headers ) ) {
			return array(
				'id'          => 'server-info-disclosure',
				'title'       => 'Server Information Disclosure',
				'description' => sprintf(
					'Your server reveals version information: %s. Remove or obscure these headers to prevent targeted attacks.',
					implode( ', ', $disclosed_headers )
				),
				'severity'    => 'low',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/hide-server-information/',
				'training_link' => 'https://wpshadow.com/training/server-hardening/',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}
		
		return null;
	}

}