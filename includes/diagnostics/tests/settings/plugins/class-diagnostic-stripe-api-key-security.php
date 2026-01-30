<?php
/**
 * Stripe Api Key Security Diagnostic
 *
 * Stripe Api Key Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1388.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Api Key Security Diagnostic Class
 *
 * @since 1.1388.0000
 */
class Diagnostic_StripeApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'stripe-api-key-security';
	protected static $title = 'Stripe Api Key Security';
	protected static $description = 'Stripe Api Key Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Stripe' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify Stripe API keys are not using test mode in production
		$test_mode = get_option( 'woocommerce_stripe_testmode', 'no' );
		if ( 'yes' === $test_mode && ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$issues[] = 'test_mode_in_production';
		}
		
		// Check 2: Verify API keys are configured
		$publishable_key = get_option( 'woocommerce_stripe_publishable_key', '' );
		$secret_key = get_option( 'woocommerce_stripe_secret_key', '' );
		
		if ( empty( $publishable_key ) || empty( $secret_key ) ) {
			$issues[] = 'missing_api_keys';
		}
		
		// Check 3: Verify API keys are not hardcoded in wp-config.php (should use options)
		if ( defined( 'STRIPE_PUBLISHABLE_KEY' ) || defined( 'STRIPE_SECRET_KEY' ) ) {
			$issues[] = 'hardcoded_api_keys';
		}
		
		// Check 4: Verify webhook secret is configured
		$webhook_secret = get_option( 'woocommerce_stripe_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = 'missing_webhook_secret';
		}
		
		// Check 5: Check if API keys follow correct format
		if ( ! empty( $publishable_key ) && ! preg_match( '/^pk_(test|live)_/', $publishable_key ) ) {
			$issues[] = 'invalid_publishable_key_format';
		}
		
		if ( ! empty( $secret_key ) && ! preg_match( '/^sk_(test|live)_/', $secret_key ) ) {
			$issues[] = 'invalid_secret_key_format';
		}
		
		// Check 6: Verify keys are not exposed in frontend JavaScript
		// Check if secret key is accidentally used instead of publishable key
		if ( ! empty( $secret_key ) && strpos( $secret_key, 'sk_' ) === 0 ) {
			// This is expected, but verify it's not exposed in localized scripts
			global $wp_scripts;
			if ( isset( $wp_scripts->registered['wc-stripe'] ) ) {
				$stripe_script = $wp_scripts->registered['wc-stripe'];
				if ( isset( $stripe_script->extra['data'] ) && strpos( $stripe_script->extra['data'], $secret_key ) !== false ) {
					$issues[] = 'secret_key_exposed_in_frontend';
				}
			}
		}
		
		// Check 7: Verify SSL is enabled when using live keys
		if ( ! empty( $secret_key ) && strpos( $secret_key, 'sk_live_' ) === 0 && ! is_ssl() ) {
			$issues[] = 'ssl_not_enabled_with_live_keys';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of Stripe API key security issues */
				__( 'Stripe payment gateway has API key security issues: %s. Compromised API keys could lead to unauthorized payment processing and financial fraud.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/stripe-api-key-security',
			);
		}
		
		return null;
	}
}
