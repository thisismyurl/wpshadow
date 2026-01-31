<?php
/**
 * Wordpress Xml Rpc Disabled Diagnostic
 *
 * Wordpress Xml Rpc Disabled issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1250.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Xml Rpc Disabled Diagnostic Class
 *
 * @since 1.1250.0000
 */
class Diagnostic_WordpressXmlRpcDisabled extends Diagnostic_Base {

	protected static $slug = 'wordpress-xml-rpc-disabled';
	protected static $title = 'Wordpress Xml Rpc Disabled';
	protected static $description = 'Wordpress Xml Rpc Disabled issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress core feature - no version check needed
		$issues = array();
		
		// Check 1: Verify if XML-RPC is enabled (security risk)
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );
		if ( $xmlrpc_enabled ) {
			$issues[] = 'xmlrpc_enabled';
		}
		
		// Check 2: Check if Pingback functionality is enabled (DDoS attack vector)
		$pingback_enabled = get_option( 'default_pingback_flag', 1 );
		if ( $pingback_enabled ) {
			$issues[] = 'pingback_enabled';
		}
		
		// Check 3: Verify XML-RPC authentication methods
		// Check if XML-RPC file exists and is accessible
		$xmlrpc_file = ABSPATH . 'xmlrpc.php';
		if ( file_exists( $xmlrpc_file ) ) {
			// File exists - check if it's been disabled via .htaccess or plugin
			$response = wp_remote_get( site_url( 'xmlrpc.php' ), array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				// 200 means accessible, 403/404 means blocked
				if ( 200 === $status_code ) {
					$issues[] = 'xmlrpc_file_accessible';
				}
			}
		}
		
		// Check 4: Check for recent XML-RPC brute force attempts
		global $wpdb;
		$xmlrpc_attempts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}wpshadow_activity_log 
				WHERE action_type = %s AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
				'xmlrpc_auth_attempt'
			)
		);
		
		if ( $xmlrpc_attempts > 100 ) {
			$issues[] = 'high_xmlrpc_auth_attempts';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of XML-RPC security issues */
				__( 'XML-RPC endpoint has security concerns: %s. XML-RPC is commonly exploited for brute force attacks, DDoS amplification, and credential stuffing. Recommended to disable unless specifically needed for mobile apps or remote publishing.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 65,
				'threat_level' => 65,
				'auto_fixable' => false, // Requires plugin or .htaccess modification
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-xml-rpc-disabled',
			);
		}
		
		return null;
	}
}
