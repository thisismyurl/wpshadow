<?php
/**
 * Email Domain Blacklist Status Diagnostic
 *
 * Checks if the site domain or sending IP is listed on major email blacklists
 * (Spamhaus, SORBS, Barracuda), which can prevent email delivery completely.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Email
 * @since      1.6028.2115
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Domain Blacklist Status Diagnostic Class
 *
 * Queries major DNSBL (DNS-based Blackhole List) services to check if the
 * site's domain or sending IP address is blacklisted, which causes complete
 * email delivery failure to major providers.
 *
 * @since 1.6028.2115
 */
class Diagnostic_Email_Domain_Blacklist_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-domain-blacklist-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Domain Blacklist Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if domain or sending IP is listed on major email blacklists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the diagnostic check.
	 *
	 * Queries multiple DNS-based blacklist services to check if the site's
	 * domain or server IP is listed, which prevents email delivery.
	 *
	 * @since  1.6028.2115
	 * @return array|null Finding array if blacklisted, null otherwise.
	 */
	public static function check() {
		// Get site domain.
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( ! $domain ) {
			return null;
		}

		// Get server IP.
		$server_ip = self::get_server_ip();

		// Check domain and IP against blacklists.
		$domain_blacklists = self::check_domain_blacklists( $domain );
		$ip_blacklists     = $server_ip ? self::check_ip_blacklists( $server_ip ) : array();

		$all_blacklists = array_merge( $domain_blacklists, $ip_blacklists );

		if ( empty( $all_blacklists ) ) {
			return null; // Not blacklisted.
		}

		// Calculate severity based on number of blacklists.
		$count        = count( $all_blacklists );
		$threat_level = min( 100, 70 + ( $count * 10 ) ); // 70 for 1, 80 for 2, etc.

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of blacklists, 2: domain or IP */
				__( 'Your domain or server IP is listed on %1$d email blacklist(s), which will prevent email delivery', 'wpshadow' ),
				$count
			),
			'severity'    => $count > 2 ? 'critical' : 'high',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/email-blacklist-removal',
			'details'     => array(
				'domain'      => $domain,
				'server_ip'   => $server_ip,
				'blacklists'  => $all_blacklists,
				'count'       => $count,
				'impact'      => __( 'Complete email delivery failure to major providers', 'wpshadow' ),
				'resolution'  => __( 'Contact blacklist operators for removal, fix underlying issues', 'wpshadow' ),
			),
			'solution'    => array(
				'free'     => array(
					__( 'Check removal procedures at each blacklist website', 'wpshadow' ),
					__( 'Fix SPF, DKIM, DMARC records if missing', 'wpshadow' ),
					__( 'Ensure no spam/malware on site causing listings', 'wpshadow' ),
					__( 'Request removal once issues resolved', 'wpshadow' ),
				),
				'premium'  => array(
					__( 'Contact hosting provider for IP reputation help', 'wpshadow' ),
					__( 'Consider dedicated IP or email service provider', 'wpshadow' ),
					__( 'Use professional blacklist monitoring service', 'wpshadow' ),
				),
				'advanced' => array(
					__( 'Migrate to dedicated sending IP with clean reputation', 'wpshadow' ),
					__( 'Implement email authentication triad (SPF+DKIM+DMARC)', 'wpshadow' ),
					__( 'Use transactional email service (SendGrid, Postmark, Mailgun)', 'wpshadow' ),
				),
			),
			'resources'   => array(
				array(
					'title' => __( 'Spamhaus Removal Guide', 'wpshadow' ),
					'url'   => 'https://www.spamhaus.org/lookup/',
				),
				array(
					'title' => __( 'MXToolbox Blacklist Check', 'wpshadow' ),
					'url'   => 'https://mxtoolbox.com/blacklists.aspx',
				),
				array(
					'title' => __( 'Email Deliverability Best Practices', 'wpshadow' ),
					'url'   => 'https://postmarkapp.com/guides/email-deliverability',
				),
			),
		);
	}

	/**
	 * Get server IP address.
	 *
	 * Attempts to determine the server's public IP address for blacklist checking.
	 *
	 * @since  1.6028.2115
	 * @return string|null Server IP address or null if cannot determine.
	 */
	private static function get_server_ip() {
		// Try $_SERVER variables.
		if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
		}

		// Try gethostbyname.
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( $domain ) {
			$ip = gethostbyname( $domain );
			// Verify it's actually an IP (not the domain echoed back).
			if ( $ip !== $domain && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return null;
	}

	/**
	 * Check domain against DNS-based blacklists.
	 *
	 * Queries major DNSBL services for domain reputation.
	 *
	 * @since  1.6028.2115
	 * @param  string $domain Domain to check.
	 * @return array Array of blacklists the domain is listed on.
	 */
	private static function check_domain_blacklists( $domain ) {
		$blacklists = array();

		// Major domain blacklists (DBL format).
		$dbl_services = array(
			'dbl.spamhaus.org'    => 'Spamhaus Domain Block List',
			'multi.surbl.org'     => 'SURBL Multi',
			'dnsbl.sorbs.net'     => 'SORBS DNSBL',
			'bl.spamcop.net'      => 'SpamCop',
			'truncate.gbudb.net'  => 'GBUdb Truncate',
		);

		foreach ( $dbl_services as $service => $name ) {
			$query = $domain . '.' . $service;

			// Query DNS.
			$result = dns_get_record( $query, DNS_A );

			// If record exists, domain is blacklisted.
			if ( ! empty( $result ) ) {
				$blacklists[] = array(
					'service' => $name,
					'type'    => 'domain',
					'query'   => $query,
					'result'  => $result[0]['ip'] ?? '',
				);
			}
		}

		return $blacklists;
	}

	/**
	 * Check IP address against DNS-based blacklists.
	 *
	 * Queries major DNSBL services for IP reputation using reverse IP format.
	 *
	 * @since  1.6028.2115
	 * @param  string $ip IP address to check.
	 * @return array Array of blacklists the IP is listed on.
	 */
	private static function check_ip_blacklists( $ip ) {
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return array(); // Only IPv4 supported.
		}

		$blacklists = array();

		// Major IP blacklists.
		$rbl_services = array(
			'zen.spamhaus.org'         => 'Spamhaus ZEN',
			'bl.spamcop.net'           => 'SpamCop',
			'b.barracudacentral.org'   => 'Barracuda',
			'dnsbl.sorbs.net'          => 'SORBS DNSBL',
			'cbl.abuseat.org'          => 'Composite Blocking List',
			'psbl.surriel.com'         => 'Passive Spam Block List',
			'dnsbl-1.uceprotect.net'   => 'UCEProtect Level 1',
		);

		// Reverse the IP for DNSBL queries.
		$reversed_ip = self::reverse_ip( $ip );

		foreach ( $rbl_services as $service => $name ) {
			$query = $reversed_ip . '.' . $service;

			// Query DNS.
			$result = dns_get_record( $query, DNS_A );

			// If record exists, IP is blacklisted.
			if ( ! empty( $result ) ) {
				$blacklists[] = array(
					'service' => $name,
					'type'    => 'ip',
					'ip'      => $ip,
					'query'   => $query,
					'result'  => $result[0]['ip'] ?? '',
				);
			}
		}

		return $blacklists;
	}

	/**
	 * Reverse IP address for DNSBL queries.
	 *
	 * Converts 1.2.3.4 to 4.3.2.1 for DNSBL query format.
	 *
	 * @since  1.6028.2115
	 * @param  string $ip IP address to reverse.
	 * @return string Reversed IP address.
	 */
	private static function reverse_ip( $ip ) {
		$octets = explode( '.', $ip );
		return implode( '.', array_reverse( $octets ) );
	}
}
