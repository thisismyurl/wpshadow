<?php
/**
 * No XML-RPC Protection Diagnostic
 *
 * Detects when XML-RPC is exposed and unprotected,
 * enabling brute force and DDoS attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No XML-RPC Protection
 *
 * Checks whether XML-RPC is disabled or protected
 * from abuse and attacks.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_XML_RPC_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-xml-rpc-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether XML-RPC is protected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if XML-RPC is accessible
		$xmlrpc_url = home_url( '/xmlrpc.php' );
		$xmlrpc_check = wp_remote_post( $xmlrpc_url, array(
			'timeout' => 5,
			'body'    => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
		) );

		$xmlrpc_accessible = ! is_wp_error( $xmlrpc_check ) && wp_remote_retrieve_response_code( $xmlrpc_check ) === 200;

		if ( $xmlrpc_accessible ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'XML-RPC is exposed and unprotected, which enables brute force attacks and DDoS amplification. XML-RPC (xmlrpc.php) is an old API that: allows remote publishing (rarely used now), enables authentication bypass attempts, can be used for DDoS amplification (one request = hundreds of checks). Unless you\'re using Jetpack or remote publishing, you should disable it. Security plugins can block XML-RPC or restrict it to trusted IPs.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Attack Surface Reduction',
					'potential_gain' => 'Block common DDoS and brute force vector',
					'roi_explanation' => 'Disabling XML-RPC prevents brute force amplification attacks and DDoS abuse vectors.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/xml-rpc-protection',
			);
		}

		return null;
	}
}
