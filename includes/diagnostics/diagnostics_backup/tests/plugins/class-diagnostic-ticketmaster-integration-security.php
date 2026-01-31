<?php
/**
 * Ticketmaster Integration Security Diagnostic
 *
 * Ticketmaster integration credentials exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.583.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticketmaster Integration Security Diagnostic Class
 *
 * @since 1.583.0000
 */
class Diagnostic_TicketmasterIntegrationSecurity extends Diagnostic_Base {

	protected static $slug = 'ticketmaster-integration-security';
	protected static $title = 'Ticketmaster Integration Security';
	protected static $description = 'Ticketmaster integration credentials exposed';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;
		
		// Check if Ticketmaster integration is present (common option patterns)
		$tm_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'%ticketmaster%',
				'%tm_api%'
			)
		);
		
		if ( empty( $tm_options ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key exposed in options
		foreach ( $tm_options as $option ) {
			if ( stripos( $option->option_name, 'api_key' ) !== false || stripos( $option->option_name, 'secret' ) !== false ) {
				if ( ! empty( $option->option_value ) && strlen( $option->option_value ) > 10 ) {
					$issues[] = sprintf( __( 'API credentials stored in option: %s', 'wpshadow' ), $option->option_name );
				}
			}
		}
		
		// Check 2: API keys in postmeta
		$tm_postmeta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value LIKE %s",
				'%ticketmaster%',
				'%key%'
			)
		);
		
		if ( $tm_postmeta > 0 ) {
			$issues[] = sprintf( __( '%d posts with Ticketmaster API data in postmeta', 'wpshadow' ), $tm_postmeta );
		}
		
		// Check 3: Check if credentials are encrypted
		$has_encryption = false;
		foreach ( $tm_options as $option ) {
			if ( stripos( $option->option_value, 'encrypted:' ) === 0 || function_exists( 'openssl_encrypt' ) ) {
				$has_encryption = true;
				break;
			}
		}
		
		if ( ! $has_encryption && count( $tm_options ) > 0 ) {
			$issues[] = __( 'Ticketmaster credentials not encrypted', 'wpshadow' );
		}
		
		// Check 4: SSL requirement for API calls
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using SSL for API communications', 'wpshadow' );
		}
		
		// Check 5: Check for webhook endpoint security
		$tm_webhook = get_option( 'ticketmaster_webhook_url', '' );
		if ( ! empty( $tm_webhook ) && strpos( $tm_webhook, 'https://' ) !== 0 ) {
			$issues[] = __( 'Ticketmaster webhook URL not using HTTPS', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 80;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Ticketmaster integration has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/ticketmaster-integration-security',
		);
	}
}
