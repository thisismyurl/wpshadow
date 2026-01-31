<?php
/**
 * Square Access Token Security Diagnostic
 *
 * Square Access Token Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1403.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Square Access Token Security Diagnostic Class
 *
 * @since 1.1403.0000
 */
class Diagnostic_SquareAccessTokenSecurity extends Diagnostic_Base {

	protected static $slug = 'square-access-token-security';
	protected static $title = 'Square Access Token Security';
	protected static $description = 'Square Access Token Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce_Square_Loader' ) && ! defined( 'WC_SQUARE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: SSL for API calls.
		if ( ! is_ssl() ) {
			$issues[] = 'API calls without HTTPS';
		}
		
		// Check 2: Access token storage.
		$access_token = get_option( 'wc_square_access_token', '' );
		if ( ! empty( $access_token ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'access token visible with debug on';
		}
		
		// Check 3: Token encryption.
		$token_encrypted = get_option( 'wc_square_encrypt_tokens', '1' );
		if ( '0' === $token_encrypted ) {
			$issues[] = 'token encryption disabled';
		}
		
		// Check 4: Sandbox mode.
		$sandbox = get_option( 'wc_square_sandbox', '0' );
		if ( '1' === $sandbox ) {
			$issues[] = 'sandbox mode on live site';
		}
		
		// Check 5: Token refresh.
		$last_refresh = get_option( 'wc_square_token_refresh', 0 );
		if ( 0 === $last_refresh || ( time() - $last_refresh > 7776000 ) ) {
			$issues[] = 'token not refreshed in 90 days';
		}
		
		// Check 6: Webhook validation.
		$webhook_validation = get_option( 'wc_square_validate_webhooks', '1' );
		if ( '0' === $webhook_validation ) {
			$issues[] = 'webhook validation disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 75 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Square security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/square-access-token-security',
			);
		}
		
		return null;
	}
}
