<?php
/**
 * A Records Verification Diagnostic
 *
 * Checks if domain A records point to the correct server IP.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A Records Verification Diagnostic Class
 *
 * Verifies DNS A records point to correct IP address.
 * Like checking your mailing address is correct on envelopes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_DNS_A_Records extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-a-records';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS A Records Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if domain A records point to the correct server IP';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns';

	/**
	 * Run the DNS A records diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if A record issues detected, null otherwise.
	 */
	public static function check() {
		// Get domain from site URL.
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		$domain = $parsed['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Remove www prefix for DNS check.
		$dns_domain = preg_replace( '/^www\./', '', $domain );

		// Check if dns_get_record is available.
		if ( ! function_exists( 'dns_get_record' ) ) {
			return null;
		}

		// Get A records.
		$records = @dns_get_record( $dns_domain, DNS_A );
		
		if ( false === $records || empty( $records ) ) {
			return array(
				'id'           => self::$slug . '-not-found',
				'title'        => __( 'DNS A Records Not Found', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'We couldn\'t find DNS A records for %s (like having no forwarding address for your mail). This means visitors can\'t find your site. If you just changed hosting or domain settings, DNS changes can take 24-48 hours to spread globally. If this persists, check your domain registrar or DNS provider settings.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-records',
				'context'      => array(
					'domain' => $domain,
				),
			);
		}

		// Get server IP.
		$server_ip = $_SERVER['SERVER_ADDR'] ?? '';
		if ( empty( $server_ip ) && isset( $_SERVER['LOCAL_ADDR'] ) ) {
			$server_ip = $_SERVER['LOCAL_ADDR'];
		}

		// Check if any A record matches server IP.
		$a_record_ips = wp_list_pluck( $records, 'ip' );
		$match_found = in_array( $server_ip, $a_record_ips, true );

		// If behind CloudFlare/CDN, server IP won't match (this is expected).
		$cloudflare_ips = array(
			'103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
			'104.16.0.0/13', '104.24.0.0/14', '108.162.192.0/18',
			'131.0.72.0/22', '141.101.64.0/18', '162.158.0.0/15',
			'172.64.0.0/13', '173.245.48.0/20', '188.114.96.0/20',
			'190.93.240.0/20', '197.234.240.0/22', '198.41.128.0/17',
		);

		$uses_cdn = false;
		foreach ( $a_record_ips as $ip ) {
			foreach ( $cloudflare_ips as $range ) {
				if ( self::ip_in_range( $ip, $range ) ) {
					$uses_cdn = true;
					break 2;
				}
			}
		}

		if ( ! $match_found && ! $uses_cdn && ! empty( $server_ip ) ) {
			return array(
				'id'           => self::$slug . '-mismatch',
				'title'        => __( 'DNS A Records Don\'t Match Server IP', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: DNS IPs, 2: server IP */
					__( 'Your DNS A records point to %1$s, but your server\'s IP is %2$s (like mail being forwarded to the wrong address). This typically happens after migrating to a new host. Update your DNS A records at your domain registrar to point to the new IP address. Changes take 24-48 hours to fully propagate.', 'wpshadow' ),
					implode( ', ', $a_record_ips ),
					esc_html( $server_ip )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-records',
				'context'      => array(
					'dns_ips'   => $a_record_ips,
					'server_ip' => $server_ip,
				),
			);
		}

		// Check for multiple A records (round-robin/load balancing).
		if ( count( $a_record_ips ) > 2 ) {
			return array(
				'id'           => self::$slug . '-multiple',
				'title'        => __( 'Multiple DNS A Records Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of A records */
					__( 'Your domain has %d A records (like having multiple forwarding addresses). This is usually for load balancing or redundancy, which is good. However, verify all IPs point to valid servers—outdated records can cause intermittent site access issues for some visitors.', 'wpshadow' ),
					count( $a_record_ips )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-records',
				'context'      => array(
					'a_records' => $a_record_ips,
				),
			);
		}

		return null; // A records configured correctly.
	}

	/**
	 * Check if an IP is in a given CIDR range.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip    IP address to check.
	 * @param  string $range CIDR range (e.g., '104.16.0.0/13').
	 * @return bool True if IP is in range.
	 */
	private static function ip_in_range( $ip, $range ) {
		list( $range_ip, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range_ip );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~$wildcard_decimal;
		return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
	}
}
