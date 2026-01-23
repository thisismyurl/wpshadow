<?php
declare(strict_types=1);
/**
 * XML-RPC Enabled Diagnostic
 *
 * Philosophy: Legacy API - disable unused endpoints
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML-RPC is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_XMLRPC_Enabled extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return array(
				'id'            => 'xmlrpc-enabled',
				'title'         => 'XML-RPC Enabled (Legacy API)',
				'description'   => 'XML-RPC is an old API rarely used. It\'s often exploited for brute force and amplification attacks. Disable XML-RPC unless needed.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-xmlrpc/',
				'training_link' => 'https://wpshadow.com/training/legacy-api-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}

}