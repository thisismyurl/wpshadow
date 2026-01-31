<?php
/**
 * Gravity Forms Webhook Security Diagnostic
 *
 * Gravity Forms webhooks not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.259.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Webhook Security Diagnostic Class
 *
 * @since 1.259.0000
 */
class Diagnostic_GravityFormsWebhookSecurity extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-webhook-security';
	protected static $title = 'Gravity Forms Webhook Security';
	protected static $description = 'Gravity Forms webhooks not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}
		
		// Check for webhooks configured
		$feeds = class_exists( 'GFAPI' ) ? GFAPI::get_feeds( null, null, 'webhooks' ) : array();
		
		if ( empty( $feeds ) ) {
			return null; // No webhooks configured
		}
		
		$issues = array();
		
		// Check 1: Signature validation
		$validate_signature = get_option( 'gf_webhooks_validate_signature', 'yes' );
		if ( 'no' === $validate_signature ) {
			$issues[] = __( 'Signature validation disabled (spoofing risk)', 'wpshadow' );
		}
		
		// Check 2: HTTPS enforcement
		foreach ( $feeds as $feed ) {
			if ( isset( $feed['meta']['requestURL'] ) ) {
				if ( strpos( $feed['meta']['requestURL'], 'https://' ) !== 0 ) {
					$issues[] = __( 'HTTP webhook URL (unencrypted data)', 'wpshadow' );
					break;
				}
			}
		}
		
		// Check 3: Request timeout
		$timeout = get_option( 'gf_webhooks_timeout', 30 );
		if ( $timeout > 60 ) {
			$issues[] = sprintf( __( '%d second timeout (form delays)', 'wpshadow' ), $timeout );
		}
		
		// Check 4: Retry configuration
		$max_retries = get_option( 'gf_webhooks_max_retries', 3 );
		if ( $max_retries === 0 ) {
			$issues[] = __( 'No retry attempts (missed submissions)', 'wpshadow' );
		}
		
		// Check 5: Payload size limit
		$payload_limit = get_option( 'gf_webhooks_payload_limit', 0 );
		if ( $payload_limit === 0 ) {
			$issues[] = __( 'No payload size limit (DoS risk)', 'wpshadow' );
		}
		
		// Check 6: IP whitelisting
		$ip_whitelist = get_option( 'gf_webhooks_ip_whitelist', array() );
		if ( empty( $ip_whitelist ) ) {
			$issues[] = __( 'No IP whitelist (unauthorized requests)', 'wpshadow' );
		}
		
		// Check 7: Logging enabled
		$logging = get_option( 'gf_webhooks_logging', 'no' );
		if ( 'no' === $logging ) {
			$issues[] = __( 'Logging disabled (no audit trail)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 75;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 68;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of webhook security issues */
				__( 'Gravity Forms webhooks have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-webhook-security',
		);
	}
}
