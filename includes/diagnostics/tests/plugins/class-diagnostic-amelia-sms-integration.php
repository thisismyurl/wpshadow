<?php
/**
 * Amelia SMS Integration Diagnostic
 *
 * Amelia SMS credentials exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.470.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia SMS Integration Diagnostic Class
 *
 * @since 1.470.0000
 */
class Diagnostic_AmeliaSmsIntegration extends Diagnostic_Base {

	protected static $slug = 'amelia-sms-integration';
	protected static $title = 'Amelia SMS Integration';
	protected static $description = 'Amelia SMS credentials exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: SMS API credentials.
		$api_key = get_option( 'amelia_settings_notifications_smsApiKey', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'SMS API credentials not configured';
		}

		// Check 2: Credentials exposure.
		$creds_in_config = defined( 'AMELIA_SMS_API_KEY' ) || defined( 'AMELIA_SMS_API_SECRET' );
		if ( ! $creds_in_config && empty( $api_key ) ) {
			$issues[] = 'SMS integration not set up (no credentials)';
		}

		// Check 3: SMS delivery confirmation.
		$delivery_confirm = get_option( 'amelia_settings_notifications_smsDeliveryReport', '0' );
		if ( '0' === $delivery_confirm && ! empty( $api_key ) ) {
			$issues[] = 'delivery confirmation disabled (cannot verify SMS sent)';
		}

		// Check 4: SMS rate limiting.
		$rate_limit = get_option( 'amelia_settings_notifications_smsRateLimit', 0 );
		if ( $rate_limit > 100 && ! empty( $api_key ) ) {
			$issues[] = "SMS rate limit set to {$rate_limit}/minute (may cause provider throttling)";
		}

		// Check 5: Failed SMS retry.
		$retry = get_option( 'amelia_settings_notifications_smsRetry', '0' );
		if ( '0' === $retry && ! empty( $api_key ) ) {
			$issues[] = 'failed SMS not retried (messages may not be delivered)';
		}

		// Check 6: SMS character encoding.
		$encoding = get_option( 'amelia_settings_notifications_smsEncoding', 'gsm7' );
		if ( 'unicode' === $encoding ) {
			$issues[] = 'SMS using Unicode encoding (higher cost per message)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 75 + ( count( $issues ) * 2 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Amelia SMS integration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-sms-integration',
			);
		}

		return null;
	}
}
