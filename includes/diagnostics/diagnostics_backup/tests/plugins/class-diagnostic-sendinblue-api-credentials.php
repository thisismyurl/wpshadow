<?php
/**
 * Sendinblue Api Credentials Diagnostic
 *
 * Sendinblue Api Credentials configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.730.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sendinblue Api Credentials Diagnostic Class
 *
 * @since 1.730.0000
 */
class Diagnostic_SendinblueApiCredentials extends Diagnostic_Base {

	protected static $slug = 'sendinblue-api-credentials';
	protected static $title = 'Sendinblue Api Credentials';
	protected static $description = 'Sendinblue Api Credentials configuration issues';
	protected static $family = 'security';

	public static function check() {
		// Check for Sendinblue/Brevo plugin
		if ( ! class_exists( 'WP_Sendinblue' ) && ! defined( 'SIB_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configured
		$api_key = get_option( 'ws_api_key', '' );
		if ( empty( $api_key ) ) {
			return null;
		}
		
		// Check 2: API key in database (not constants)
		if ( ! defined( 'SENDINBLUE_API_KEY' ) ) {
			$issues[] = __( 'API key stored in database (should use wp-config constant)', 'wpshadow' );
		}
		
		// Check 3: API key validation
		$key_valid = get_transient( 'sendinblue_api_key_valid' );
		if ( false === $key_valid ) {
			$issues[] = __( 'API key validation not cached (repeated API calls)', 'wpshadow' );
		}
		
		// Check 4: SMTP configuration
		$smtp_enabled = get_option( 'ws_smtp_enable', false );
		if ( $smtp_enabled ) {
			$smtp_port = get_option( 'ws_smtp_port', 587 );
			if ( 587 !== $smtp_port && 465 !== $smtp_port ) {
				$issues[] = sprintf( __( 'Unusual SMTP port: %d (standard: 587 or 465)', 'wpshadow' ), $smtp_port );
			}
		}
		
		// Check 5: List sync frequency
		$sync_enabled = get_option( 'ws_double_optin', false );
		$sync_frequency = get_option( 'ws_sync_frequency', 'immediate' );
		
		if ( 'immediate' === $sync_frequency && $sync_enabled ) {
			$issues[] = __( 'Immediate list sync (consider batching for performance)', 'wpshadow' );
		}
		
		// Check 6: Rate limit handling
		$rate_limit_errors = get_option( 'sendinblue_rate_limit_errors', 0 );
		if ( $rate_limit_errors > 10 ) {
			$issues[] = sprintf( __( '%d rate limit errors (API quota exceeded)', 'wpshadow' ), $rate_limit_errors );
		}
		
		// Check 7: Webhook security
		$webhook_enabled = get_option( 'ws_webhook_enabled', false );
		$webhook_auth = get_option( 'ws_webhook_auth_key', '' );
		
		if ( $webhook_enabled && empty( $webhook_auth ) ) {
			$issues[] = __( 'Webhook authentication not configured (security risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 78;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of API credential issues */
				__( 'Sendinblue API credentials have %d security/performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/sendinblue-api-credentials',
		);
	}
}
