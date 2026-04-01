<?php
/**
 * XML-RPC Security Diagnostic
 *
 * Issue #4912: XML-RPC Enabled (DDoS and Brute Force Vector)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if XML-RPC is enabled.
 * XML-RPC is rarely needed and often exploited.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_XML_RPC_Security Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_XML_RPC_Security extends Diagnostic_Base {

	protected static $slug = 'xml-rpc-security';
	protected static $title = 'XML-RPC Enabled (DDoS and Brute Force Vector)';
	protected static $description = 'Checks if XML-RPC interface is unnecessarily enabled';
	protected static $family = 'security';

	public static function check() {
		// Check if XML-RPC is enabled
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );

		if ( $xmlrpc_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'XML-RPC allows remote access but is exploited for DDoS amplification and brute force attacks. Disable unless using Jetpack or mobile apps.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/xml-rpc-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'attacks'                 => 'Pingback DDoS, brute force amplification',
					'amplification'           => 'One request = hundreds of login attempts',
					'jetpack_note'            => 'Jetpack requires XML-RPC (use Jetpack firewall)',
					'disable_method'          => 'add_filter("xmlrpc_enabled", "__return_false");',
				),
			);
		}

		return null;
	}
}
