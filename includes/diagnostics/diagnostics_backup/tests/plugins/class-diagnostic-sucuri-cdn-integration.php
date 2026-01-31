<?php
/**
 * Sucuri Cdn Integration Diagnostic
 *
 * Sucuri Cdn Integration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.853.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucuri Cdn Integration Diagnostic Class
 *
 * @since 1.853.0000
 */
class Diagnostic_SucuriCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'sucuri-cdn-integration';
	protected static $title = 'Sucuri Cdn Integration';
	protected static $description = 'Sucuri Cdn Integration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SUCURISCAN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Sucuri Firewall API key configured
		$api_key = get_option( 'sucuriscan_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Sucuri API key not configured', 'wpshadow' );
		}
		
		// Check 2: Firewall enabled
		$firewall_enabled = get_option( 'sucuriscan_cloudproxy_enabled', false );
		if ( ! $firewall_enabled ) {
			$issues[] = __( 'Sucuri CloudProxy firewall not enabled', 'wpshadow' );
		}
		
		// Check 3: Verify server IP is from Sucuri
		if ( $firewall_enabled ) {
			$server_ip = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';
			$cloudproxy_ip = get_option( 'sucuriscan_cloudproxy_ip', '' );
			
			if ( ! empty( $server_ip ) && ! empty( $cloudproxy_ip ) && $server_ip !== $cloudproxy_ip ) {
				$issues[] = __( 'Traffic not routing through Sucuri CloudProxy', 'wpshadow' );
			}
		}
		
		// Check 4: CDN cache settings
		$cache_mode = get_option( 'sucuriscan_cloudproxy_cache_mode', '' );
		if ( $firewall_enabled && empty( $cache_mode ) ) {
			$issues[] = __( 'CloudProxy cache mode not configured', 'wpshadow' );
		}
		
		// Check 5: Real IP header detection
		$real_ip_header = get_option( 'sucuriscan_cloudproxy_real_ip_header', '' );
		if ( $firewall_enabled && empty( $real_ip_header ) ) {
			$issues[] = __( 'Real IP header not configured (visitor tracking may fail)', 'wpshadow' );
		}
		
		// Check 6: Verify DNS points to Sucuri
		if ( $firewall_enabled ) {
			$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
			$dns_check = get_option( 'sucuriscan_dns_verified', false );
			
			if ( ! $dns_check ) {
				$issues[] = __( 'DNS not verified to point to Sucuri', 'wpshadow' );
			}
		}
		
		// Check 7: SSL certificate through Sucuri
		if ( $firewall_enabled && is_ssl() ) {
			$ssl_provider = get_option( 'sucuriscan_ssl_provider', '' );
			if ( empty( $ssl_provider ) || $ssl_provider !== 'sucuri' ) {
				$issues[] = __( 'SSL not managed through Sucuri (potential mixed content)', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of configuration issues */
				__( 'Sucuri CDN integration has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/sucuri-cdn-integration',
		);
	}
}
