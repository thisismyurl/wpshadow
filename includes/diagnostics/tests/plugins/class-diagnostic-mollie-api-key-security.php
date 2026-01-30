<?php
/**
 * Mollie Api Key Security Diagnostic
 *
 * Mollie Api Key Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1409.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mollie Api Key Security Diagnostic Class
 *
 * @since 1.1409.0000
 */
class Diagnostic_MollieApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'mollie-api-key-security';
	protected static $title = 'Mollie Api Key Security';
	protected static $description = 'Mollie Api Key Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		// Check for Mollie payment gateway
		$mollie_active = class_exists( 'Mollie_WC_Plugin' ) || function_exists( 'mollie_wc_plugin_init' );
		
		if ( ! $mollie_active ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configuration
		$api_key_live = get_option( 'mollie-payments-for-woocommerce_live_api_key', '' );
		$api_key_test = get_option( 'mollie-payments-for-woocommerce_test_api_key', '' );
		
		if ( empty( $api_key_live ) && empty( $api_key_test ) ) {
			return null;
		}
		
		// Check 2: Key storage in wp-config
		if ( ! defined( 'MOLLIE_API_KEY' ) && ! empty( $api_key_live ) ) {
			$issues[] = __( 'API key stored in database (should use wp-config constant)', 'wpshadow' );
		}
		
		// Check 3: Test mode in production
		$test_mode = get_option( 'mollie-payments-for-woocommerce_test_mode_enabled', 'no' );
		if ( 'yes' === $test_mode && wp_get_environment_type() === 'production' ) {
			$issues[] = __( 'Test mode enabled in production environment', 'wpshadow' );
		}
		
		// Check 4: Webhook signature verification
		$verify_webhooks = get_option( 'mollie_verify_webhook_signature', false );
		if ( ! $verify_webhooks ) {
			$issues[] = __( 'Webhook signature verification not enabled (spoofing risk)', 'wpshadow' );
		}
		
		// Check 5: API key in frontend JavaScript
		global $wpdb;
		$frontend_exposure = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_value LIKE %s OR meta_value LIKE %s",
				'%live_' . $wpdb->esc_like( substr( $api_key_live, 0, 10 ) ) . '%',
				'%test_' . $wpdb->esc_like( substr( $api_key_test, 0, 10 ) ) . '%'
			)
		);
		
		if ( $frontend_exposure > 0 ) {
			$issues[] = __( 'API key fragments detected in post meta (exposure risk)', 'wpshadow' );
		}
		
		// Check 6: API key encryption
		if ( ! defined( 'MOLLIE_API_KEY_ENCRYPTED' ) || ! MOLLIE_API_KEY_ENCRYPTED ) {
			$issues[] = __( 'API keys not encrypted at rest', 'wpshadow' );
		}
		
		// Check 7: Failed payment logging
		$log_failures = get_option( 'mollie_log_payment_failures', false );
		if ( ! $log_failures ) {
			$issues[] = __( 'Payment failures not logged (security monitoring)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 80;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 92;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 86;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security vulnerabilities */
				__( 'Mollie API key security has %d critical issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/mollie-api-key-security',
		);
	}
}
