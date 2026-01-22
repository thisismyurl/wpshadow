<?php
declare(strict_types=1);
/**
 * XML-RPC Security Diagnostic
 *
 * Philosophy: Security hardening - disable unused attack vector
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML-RPC is disabled.
 */
class Diagnostic_XMLRPC_Disabled extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if xmlrpc.php is accessible
		$xmlrpc_url = site_url( 'xmlrpc.php' );
		$response   = wp_remote_post(
			$xmlrpc_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
				'body'      => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
				'headers'   => array( 'Content-Type' => 'text/xml' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Can't check or blocked (good)
		}

		$status = wp_remote_retrieve_response_code( $response );

		// If XML-RPC responds, it's enabled
		if ( $status === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, 'methodResponse' ) !== false ) {
				return array(
					'id'            => 'xmlrpc-disabled',
					'title'         => 'XML-RPC Enabled',
					'description'   => 'XML-RPC is enabled and can be exploited for DDoS amplification and brute force attacks. Disable it unless you need pingbacks or remote publishing.',
					'severity'      => 'medium',
					'category'      => 'security',
					'kb_link'       => 'https://wpshadow.com/kb/disable-xmlrpc/',
					'training_link' => 'https://wpshadow.com/training/xmlrpc-security/',
					'auto_fixable'  => true,
					'threat_level'  => 70,
				);
			}
		}

		return null;
	}
}
