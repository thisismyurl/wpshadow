<?php
/**
 * XML-RPC Brute Force Protection Status Diagnostic
 *
 * Checks if XML-RPC is disabled or has brute force protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML-RPC Brute Force Protection Status Class
 *
 * Tests XML-RPC security.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Xml_Rpc_Brute_Force_Protection_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-rpc-brute-force-protection-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML-RPC Brute Force Protection Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML-RPC is disabled or has brute force protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$xmlrpc_check = self::check_xmlrpc_status();
		
		if ( $xmlrpc_check['is_vulnerable'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'XML-RPC enabled without protection (allows 1000 password attempts per request via system.multicall)', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xml-rpc-brute-force-protection-status',
				'meta'         => array(
					'xmlrpc_enabled'  => $xmlrpc_check['xmlrpc_enabled'],
					'multicall_enabled' => $xmlrpc_check['multicall_enabled'],
				),
			);
		}

		return null;
	}

	/**
	 * Check XML-RPC status.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_xmlrpc_status() {
		$check = array(
			'is_vulnerable'     => false,
			'xmlrpc_enabled'    => false,
			'multicall_enabled' => false,
		);

		// Check if XML-RPC is enabled.
		$xmlrpc_url = site_url( 'xmlrpc.php' );
		$response = wp_remote_post( $xmlrpc_url, array(
			'timeout' => 5,
			'body'    => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
		) );

		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			
			if ( 200 === $status_code ) {
				$check['xmlrpc_enabled'] = true;

				// Check if system.multicall is available.
				$body = wp_remote_retrieve_body( $response );
				
				if ( false !== strpos( $body, 'system.multicall' ) ) {
					$check['multicall_enabled'] = true;
					$check['is_vulnerable'] = true;
				}
			}
		}

		return $check;
	}
}
