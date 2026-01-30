<?php
/**
 * Wordfence Xml Rpc Blocking Diagnostic
 *
 * Wordfence Xml Rpc Blocking misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.849.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Xml Rpc Blocking Diagnostic Class
 *
 * @since 1.849.0000
 */
class Diagnostic_WordfenceXmlRpcBlocking extends Diagnostic_Base {

	protected static $slug = 'wordfence-xml-rpc-blocking';
	protected static $title = 'Wordfence Xml Rpc Blocking';
	protected static $description = 'Wordfence Xml Rpc Blocking misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$waf_options = get_option( 'wordfence_waf', array() );
		
		// Check 1: XML-RPC blocking
		$xmlrpc_blocked = isset( $waf_options['disableWAFIPBlocking'] ) && ! $waf_options['disableWAFIPBlocking'];
		if ( ! $xmlrpc_blocked ) {
			$issues[] = __( 'XML-RPC not blocked (brute force risk)', 'wpshadow' );
		}
		
		// Check 2: Pingback protection
		$pingback_disabled = get_option( 'wordfence_disable_pingback', 0 );
		if ( ! $pingback_disabled ) {
			$issues[] = __( 'Pingbacks enabled (DDoS vector)', 'wpshadow' );
		}
		
		// Check 3: Authentication over XML-RPC
		$xmlrpc_auth = get_option( 'wordfence_xmlrpc_authentication', 1 );
		if ( $xmlrpc_auth ) {
			$issues[] = __( 'XML-RPC authentication allowed (credential theft)', 'wpshadow' );
		}
		
		// Check 4: Rate limiting
		$rate_limit = get_option( 'wordfence_rate_limit_xmlrpc', 0 );
		if ( ! $rate_limit ) {
			$issues[] = __( 'No rate limiting (flood attacks)', 'wpshadow' );
		}
		
		// Check 5: Blocked XML-RPC methods
		$blocked_methods = get_option( 'wordfence_xmlrpc_blocked_methods', array() );
		if ( ! in_array( 'pingback.ping', $blocked_methods, true ) ) {
			$issues[] = __( 'Pingback.ping not blocked (abuse risk)', 'wpshadow' );
		}
		
		// Check 6: Logging
		$log_xmlrpc = get_option( 'wordfence_log_xmlrpc', 0 );
		if ( ! $log_xmlrpc ) {
			$issues[] = __( 'XML-RPC not logged (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Wordfence XML-RPC has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordfence-xml-rpc-blocking',
		);
	}
}
