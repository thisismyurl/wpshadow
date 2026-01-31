<?php
/**
 * Weglot Api Key Security Diagnostic
 *
 * Weglot Api Key Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1156.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Api Key Security Diagnostic Class
 *
 * @since 1.1156.0000
 */
class Diagnostic_WeglotApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'weglot-api-key-security';
	protected static $title = 'Weglot Api Key Security';
	protected static $description = 'Weglot Api Key Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify API key is not stored in plain text
		$api_key = get_option( 'weglot_api_key', '' );
		if ( ! empty( $api_key ) && strlen( $api_key ) < 32 ) {
			$issues[] = __( 'Weglot API key appears to be in plain text format', 'wpshadow' );
		}

		// Check 2: Check if API key is encrypted
		$key_encrypted = get_option( 'weglot_api_key_encrypted', false );
		if ( ! $key_encrypted ) {
			$issues[] = __( 'API key encryption not enabled', 'wpshadow' );
		}

		// Check 3: Verify SSL for API communications
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for Weglot API communications', 'wpshadow' );
		}

		// Check 4: Check API key rotation policy
		$last_key_rotation = get_option( 'weglot_last_key_rotation', 0 );
		if ( $last_key_rotation < ( time() - ( 180 * DAY_IN_SECONDS ) ) ) {
			$issues[] = __( 'API key not rotated in last 180 days', 'wpshadow' );
		}

		// Check 5: Verify API access logging
		$access_logging = get_option( 'weglot_api_access_logging', false );
		if ( ! $access_logging ) {
			$issues[] = __( 'API access logging not enabled', 'wpshadow' );
		}

		// Check 6: Check API key validation status
		$key_valid = get_transient( 'weglot_api_key_valid' );
		if ( false === $key_valid ) {
			$issues[] = __( 'API key validation status not cached', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
