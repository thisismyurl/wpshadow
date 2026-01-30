<?php
/**
 * Authorize Net Signature Key Diagnostic
 *
 * Authorize Net Signature Key vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1402.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authorize Net Signature Key Diagnostic Class
 *
 * @since 1.1402.0000
 */
class Diagnostic_AuthorizeNetSignatureKey extends Diagnostic_Base {

	protected static $slug = 'authorize-net-signature-key';
	protected static $title = 'Authorize Net Signature Key';
	protected static $description = 'Authorize Net Signature Key vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		// Check for Authorize.net integrations (WooCommerce, EDD, etc)
		$has_authnet = get_option( 'authnet_api_login_id', '' ) !== '' ||
		               get_option( 'woocommerce_authnet_settings', false ) !== false ||
		               get_option( 'edd_settings', array() )['gateways']['authorize'] ?? false;
		
		if ( ! $has_authnet ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Signature key configured
		$signature_key = get_option( 'authnet_signature_key', '' );
		if ( empty( $signature_key ) ) {
			$issues[] = __( 'No signature key (replay attack risk)', 'wpshadow' );
		}
		
		// Check 2: Using deprecated MD5 hash
		$use_md5 = get_option( 'authnet_use_md5_hash', 'no' );
		if ( 'yes' === $use_md5 ) {
			$issues[] = __( 'Using MD5 hash (deprecated, weak security)', 'wpshadow' );
		}
		
		// Check 3: Transaction key exposure
		$transaction_key = get_option( 'authnet_transaction_key', '' );
		if ( ! empty( $transaction_key ) && strlen( $transaction_key ) < 16 ) {
			$issues[] = __( 'Weak transaction key (brute force risk)', 'wpshadow' );
		}
		
		// Check 4: Webhook signature validation
		$validate_webhooks = get_option( 'authnet_validate_webhooks', 'no' );
		if ( 'no' === $validate_webhooks ) {
			$issues[] = __( 'Webhooks not validated (fake transactions)', 'wpshadow' );
		}
		
		// Check 5: Silent POST enabled
		$silent_post = get_option( 'authnet_silent_post_url', '' );
		if ( ! empty( $silent_post ) && strpos( $silent_post, 'https://' ) !== 0 ) {
			$issues[] = __( 'Silent POST not HTTPS (data interception)', 'wpshadow' );
		}
		
		// Check 6: Transaction logging
		$log_transactions = get_option( 'authnet_log_transactions', 'no' );
		if ( 'no' === $log_transactions ) {
			$issues[] = __( 'No transaction logging (no audit trail)', 'wpshadow' );
		}
		
		// Check 7: Test mode in production
		$test_mode = get_option( 'authnet_test_mode', 'no' );
		if ( 'yes' === $test_mode && ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$issues[] = __( 'Test mode active in production', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 80;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 95;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 88;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Authorize.net security issues */
				__( 'Authorize.net has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/authorize-net-signature-key',
		);
	}
}
