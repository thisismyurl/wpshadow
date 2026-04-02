<?php
/**
 * DNS Nameservers Health Check Diagnostic
 *
 * Validates nameserver configuration and availability.
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
 * DNS Nameservers Health Check Diagnostic Class
 *
 * Checks nameserver configuration and availability.
 * Like verifying the phone directory service is working.
 *
 * @since 1.6093.1200
 */
class Diagnostic_DNS_Nameservers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-nameservers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Nameservers Health Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates nameserver configuration and availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns';

	/**
	 * Run the DNS nameservers diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if nameserver issues detected, null otherwise.
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

		// Get NS records.
		$ns_records = @dns_get_record( $dns_domain, DNS_NS );

		if ( false === $ns_records || empty( $ns_records ) ) {
			return array(
				'id'           => self::$slug . '-not-found',
				'title'        => __( 'DNS Nameservers Not Found', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'We couldn\'t find nameserver (NS) records for %s (like having no phone company assigned to your number). This is a critical DNS configuration issue that prevents your domain from working. Check your domain registrar settings and ensure nameservers are properly configured.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-nameservers',
				'context'      => array(
					'domain' => $domain,
				),
			);
		}

		// Check for single nameserver (no redundancy).
		$nameservers = wp_list_pluck( $ns_records, 'target' );
		if ( count( $nameservers ) === 1 ) {
			return array(
				'id'           => self::$slug . '-single',
				'title'        => __( 'Only One DNS Nameserver Configured', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: nameserver */
					__( 'Your domain has only one nameserver (%s) handling DNS requests (like having only one phone line with no backup). If this server goes down, your entire site becomes inaccessible. Configure at least two nameservers for redundancy. Most DNS providers offer multiple nameservers—check your DNS provider documentation.', 'wpshadow' ),
					esc_html( $nameservers[0] )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-nameservers',
				'context'      => array(
					'nameservers' => $nameservers,
				),
			);
		}

		// Detect common DNS providers (informational).
		$nameserver_lower = strtolower( $nameservers[0] );
		$providers = array(
			'cloudflare' => 'Cloudflare',
			'awsdns'     => 'Amazon Route 53',
			'googledomains' => 'Google Domains',
			'dnsmadeeasy' => 'DNS Made Easy',
			'nsone'      => 'NS1',
			'dnsimple'   => 'DNSimple',
			'domaincontrol' => 'GoDaddy',
			'registrar-servers' => 'Namecheap',
		);

		$detected_provider = null;
		foreach ( $providers as $pattern => $name ) {
			if ( false !== strpos( $nameserver_lower, $pattern ) ) {
				$detected_provider = $name;
				break;
			}
		}

		// Check for mismatched nameserver providers (can indicate misconfiguration).
		$providers_found = array();
		foreach ( $nameservers as $ns ) {
			$ns_lower = strtolower( $ns );
			foreach ( $providers as $pattern => $name ) {
				if ( false !== strpos( $ns_lower, $pattern ) ) {
					$providers_found[ $name ] = true;
					break;
				}
			}
		}

		if ( count( $providers_found ) > 1 ) {
			return array(
				'id'           => self::$slug . '-mixed-providers',
				'title'        => __( 'Mixed DNS Nameserver Providers', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of providers */
					__( 'Your nameservers come from different DNS providers (%s), like having phone service from multiple companies. This can cause issues if providers have different DNS records. All nameservers should typically come from the same DNS provider. Verify this is intentional and not a misconfiguration from incomplete DNS changes.', 'wpshadow' ),
					implode( ', ', array_keys( $providers_found ) )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-nameservers',
				'context'      => array(
					'nameservers' => $nameservers,
					'providers'   => array_keys( $providers_found ),
				),
			);
		}

		return null; // Nameservers configured properly.
	}
}
