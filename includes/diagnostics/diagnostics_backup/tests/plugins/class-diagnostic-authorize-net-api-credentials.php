<?php
/**
 * Authorize Net Api Credentials Diagnostic
 *
 * Authorize Net Api Credentials vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1400.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authorize Net Api Credentials Diagnostic Class
 *
 * @since 1.1400.0000
 */
class Diagnostic_AuthorizeNetApiCredentials extends Diagnostic_Base {

	protected static $slug = 'authorize-net-api-credentials';
	protected static $title = 'Authorize Net Api Credentials';
	protected static $description = 'Authorize Net Api Credentials vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		// Check for Authorize.Net integration (common option patterns)
		global $wpdb;
		$authnet_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'%authorize%',
				'%authnet%'
			)
		);
		
		if ( empty( $authnet_options ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API login ID and transaction key in database
		$credentials_in_db = false;
		foreach ( $authnet_options as $option ) {
			if ( stripos( $option->option_name, 'api_login' ) !== false || stripos( $option->option_name, 'transaction_key' ) !== false ) {
				if ( ! empty( $option->option_value ) ) {
					$credentials_in_db = true;
					$issues[] = sprintf( __( 'API credentials in database: %s (should use wp-config.php)', 'wpshadow' ), $option->option_name );
				}
			}
		}
		
		// Check 2: Verify constants are defined in wp-config
		if ( ! $credentials_in_db && ! defined( 'AUTHNET_API_LOGIN_ID' ) && ! defined( 'AUTHNET_TRANSACTION_KEY' ) ) {
			$issues[] = __( 'Authorize.Net credentials not configured', 'wpshadow' );
		}
		
		// Check 3: Sandbox mode detection
		$sandbox_mode = get_option( 'authnet_sandbox_mode', false );
		$sandbox_url = get_option( 'authnet_api_endpoint', '' );
		
		if ( $sandbox_mode || stripos( $sandbox_url, 'sandbox' ) !== false ) {
			if ( ! wp_get_environment_type() === 'development' ) {
				$issues[] = __( 'Authorize.Net sandbox mode on production site', 'wpshadow' );
			}
		}
		
		// Check 4: SSL enforcement
		if ( ! is_ssl() ) {
			$issues[] = __( 'Authorize.Net requires SSL for PCI compliance', 'wpshadow' );
		}
		
		// Check 5: Transaction logging
		$logging = get_option( 'authnet_logging_enabled', false );
		if ( ! $logging ) {
			$issues[] = __( 'Transaction logging not enabled (troubleshooting difficult)', 'wpshadow' );
		}
		
		// Check 6: Webhook signature verification
		$webhook_secret = get_option( 'authnet_webhook_signature_key', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = __( 'Webhook signature key not configured (security risk)', 'wpshadow' );
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
				/* translators: %s: list of security issues */
				__( 'Authorize.Net API has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/authorize-net-api-credentials',
		);
	}
}
