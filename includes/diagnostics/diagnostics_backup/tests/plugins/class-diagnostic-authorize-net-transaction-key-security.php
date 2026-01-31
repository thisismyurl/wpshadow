<?php
/**
 * Authorize Net Transaction Key Security Diagnostic
 *
 * Authorize Net Transaction Key Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1401.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authorize Net Transaction Key Security Diagnostic Class
 *
 * @since 1.1401.0000
 */
class Diagnostic_AuthorizeNetTransactionKeySecurity extends Diagnostic_Base {

	protected static $slug = 'authorize-net-transaction-key-security';
	protected static $title = 'Authorize Net Transaction Key Security';
	protected static $description = 'Authorize Net Transaction Key Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'AuthorizeNetPaymentGateway' ) && ! function_exists( 'wc_anet' ) ) {
			return null;
		}
		$issues = array();
		$api_login = get_option( 'anet_api_login', '' );
		if ( empty( $api_login ) ) { $issues[] = 'API login not configured'; }
		$transaction_key = get_option( 'anet_transaction_key', '' );
		if ( empty( $transaction_key ) ) { $issues[] = 'transaction key not configured'; }
		$signature_key = get_option( 'anet_signature_key', '' );
		if ( empty( $signature_key ) ) { $issues[] = 'signature key not set'; }
		if ( is_ssl() ) {} else { $issues[] = 'SSL not enabled for API calls'; }
		$test_mode = get_option( 'anet_test_mode', 0 );
		if ( '1' === $test_mode ) { $issues[] = 'test mode still active in production'; }
		$key_rotation = get_option( 'anet_last_key_rotation', 0 );
		if ( $key_rotation && ( time() - (int) $key_rotation > 15778800 ) ) { $issues[] = 'keys not rotated in 6+ months'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 95, 80 + ( count( $issues ) * 3 ) ) ), 'threat_level' => min( 95, 80 + ( count( $issues ) * 3 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/authorize-net-transaction-key-security' );
		}
		return null;
	}
}
