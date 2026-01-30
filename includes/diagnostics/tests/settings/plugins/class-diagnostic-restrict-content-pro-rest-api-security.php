<?php
/**
 * Restrict Content Pro REST API Security Diagnostic
 *
 * RCP REST API endpoints exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.332.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro REST API Security Diagnostic Class
 *
 * @since 1.332.0000
 */
class Diagnostic_RestrictContentProRestApiSecurity extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-rest-api-security';
	protected static $title = 'Restrict Content Pro REST API Security';
	protected static $description = 'RCP REST API endpoints exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify REST API endpoints require authentication
		$rest_url = rest_url( 'rcp/v1/members' );
		$response = wp_remote_get( $rest_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $status_code ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( is_array( $data ) && ! empty( $data ) ) {
					$issues[] = 'members_endpoint_exposed';
				}
			}
		}
		
		// Check 2: Check subscriptions endpoint
		$subs_url = rest_url( 'rcp/v1/subscriptions' );
		$response = wp_remote_get( $subs_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $status_code ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( is_array( $data ) && ! empty( $data ) ) {
					$issues[] = 'subscriptions_endpoint_exposed';
				}
			}
		}
		
		// Check 3: Verify API key authentication is configured
		$api_key_enabled = get_option( 'rcp_enable_rest_api_keys', 'no' );
		if ( 'no' === $api_key_enabled ) {
			$issues[] = 'api_key_authentication_disabled';
		}
		
		// Check 4: Check for API keys without IP restrictions
		global $wpdb;
		$api_keys_table = $wpdb->prefix . 'rcp_api_keys';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$api_keys_table}'" ) === $api_keys_table ) {
			$unrestricted_keys = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$api_keys_table} 
					WHERE (allowed_ips IS NULL OR allowed_ips = %s)
					AND status = %s",
					'',
					'active'
				)
			);
			
			if ( $unrestricted_keys > 0 ) {
				$issues[] = 'api_keys_without_ip_restrictions';
			}
			
			// Check 5: Check for API keys without expiration
			$keys_no_expiry = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$api_keys_table} 
					WHERE (expiration IS NULL OR expiration = %s)
					AND status = %s",
					'0000-00-00 00:00:00',
					'active'
				)
			);
			
			if ( $keys_no_expiry > 0 ) {
				$issues[] = 'api_keys_without_expiration';
			}
		}
		
		// Check 6: Verify webhook signature validation is enabled
		$webhook_signature = get_option( 'rcp_webhook_verify_signature', 'no' );
		if ( 'no' === $webhook_signature ) {
			$issues[] = 'webhook_signature_validation_disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of REST API security issues */
				__( 'Restrict Content Pro REST API has security issues: %s. Exposed membership and subscription data can lead to unauthorized access and data breaches.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/restrict-content-pro-rest-api-security',
			);
		}
		
		return null;
	}
}
