<?php declare(strict_types=1);
/**
 * XML-RPC Enabled Diagnostic
 *
 * Philosophy: Legacy API - disable unused endpoints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if XML-RPC is enabled.
 */
class Diagnostic_XMLRPC_Enabled {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return array(
				'id'          => 'xmlrpc-enabled',
				'title'       => 'XML-RPC Enabled (Legacy API)',
				'description' => 'XML-RPC is an old API rarely used. It\'s often exploited for brute force and amplification attacks. Disable XML-RPC unless needed.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-xmlrpc/',
				'training_link' => 'https://wpshadow.com/training/legacy-api-security/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}
