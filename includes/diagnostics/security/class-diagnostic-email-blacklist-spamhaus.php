<?php
/**
 * Email Blacklist Check Diagnostic
 *
 * Checks if the site's IP address or domain is listed in major email blacklists
 * (Spamhaus, Barracuda, etc.), which would cause email delivery problems.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Email_Blacklist_Spamhaus Class
 *
 * Checks if the server's IP address or domain is listed on email blacklists (RBLs).
 * These listings severely impact email deliverability—legitimate emails from your
 * site get marked as spam or rejected outright.
 *
 * Uses DNS queries to check against multiple RBLs:
 * - Spamhaus (SBL, CSS, PBL)
 * - Barracuda (BRBL)
 * - PSBL
 * - And others...
 *
 * This is a free check using DNS - no API key required.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Blacklist_Spamhaus extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'email-blacklist-spamhaus';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Email Blacklist Check';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if your domain/IP is listed in email blacklists';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * List of RBLs (Realtime Blackhole Lists) to check.
	 *
	 * Format: array(
	 *     'rbl_dns_suffix' => 'Human readable name',
	 *     ...
	 * )
	 *
	 * @var array
	 */
	const RBLS = array(
		'zen.spamhaus.org'          => 'Spamhaus ZEN (SBL/CSS/PBL)',
		'b.barracudacentral.org'    => 'Barracuda BRBL',
		'psbl.surriel.com'          => 'PSBL',
		'dnsbl.sorbs.net'           => 'SORBS',
	);

	/**
	 * Cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400;

	/**
	 * Run the diagnostic check.
	 *
	 * Retrieves server IP and domain, then checks against multiple RBLs.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if listed on blacklists, null otherwise.
	 */
	public static function check() {
		// Get server IP and domain.
		$server_ip = self::get_server_ip();
		$site_domain = self::get_site_domain();

		// Check cache first.
		$cache_key = 'wpshadow_blacklist_' . sanitize_key( $server_ip . '_' . $site_domain );
		$cached_result = get_transient( $cache_key );
		if ( false !== $cached_result ) {
			if ( empty( $cached_result ) ) {
				return null; // Cached "not listed" result.
			}
			return $cached_result; // Cached listing result.
		}

		// Check RBLs.
		$blacklist_results = array();

		// Check IP address.
		if ( ! empty( $server_ip ) ) {
			$ip_check = self::check_rbl_ip( $server_ip );
			if ( ! empty( $ip_check ) ) {
				$blacklist_results = array_merge( $blacklist_results, $ip_check );
			}
		}

		// Check domain.
		if ( ! empty( $site_domain ) ) {
			$domain_check = self::check_rbl_domain( $site_domain );
			if ( ! empty( $domain_check ) ) {
				$blacklist_results = array_merge( $blacklist_results, $domain_check );
			}
		}

		// Cache the result (24 hours).
		set_transient( $cache_key, $blacklist_results, self::CACHE_TTL );

		// No listings found.
		if ( empty( $blacklist_results ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $blacklist_results );
		$threat_level = self::calculate_threat_level( $blacklist_results );
		$description  = self::build_description( $server_ip, $site_domain, $blacklist_results );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $blacklist_results,
			'item_count'      => count( $blacklist_results ),
			'kb_link'         => 'https://wpshadow.com/kb/blacklist-delisting',
		);
	}

	/**
	 * Get server's public IP address.
	 *
	 * @since 1.6093.1200
	 * @return string|null Server IP or null if unable to determine.
	 */
	private static function get_server_ip() {
		// Check REMOTE_ADDR.
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( self::is_valid_ip( $remote_addr ) && ! self::is_private_ip( $remote_addr ) ) {
				return $remote_addr;
			}
		}

		// Check X-Forwarded-For.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ips = explode( ',', $forwarded );
			foreach ( $ips as $ip ) {
				$ip = trim( $ip );
				if ( self::is_valid_ip( $ip ) && ! self::is_private_ip( $ip ) ) {
					return $ip;
				}
			}
		}

		return null;
	}

	/**
	 * Get site domain.
	 *
	 * @since 1.6093.1200
	 * @return string|null Site domain or null.
	 */
	private static function get_site_domain() {
		$home_url = home_url();
		if ( ! empty( $home_url ) ) {
			$parsed = wp_parse_url( $home_url );
			return $parsed['host'] ?? null;
		}
		return null;
	}

	/**
	 * Check if IP is valid.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address.
	 * @return bool True if valid IPv4/IPv6.
	 */
	private static function is_valid_ip( string $ip ) : bool {
		return (bool) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Check if IP is private/reserved.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address.
	 * @return bool True if private.
	 */
	private static function is_private_ip( string $ip ) : bool {
		return (bool) filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	/**
	 * Check IP address against RBLs.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address to check.
	 * @return array Array of listings, empty if not listed.
	 */
	private static function check_rbl_ip( string $ip ) : array {
		$listings = array();

		// Reverse the IP octets for RBL lookup.
		$reversed_ip = self::reverse_ip_octets( $ip );
		if ( empty( $reversed_ip ) ) {
			return $listings;
		}

		// Check against each RBL.
		foreach ( self::RBLS as $rbl_suffix => $rbl_name ) {
			$lookup_host = $reversed_ip . '.' . $rbl_suffix;

			// Use checkdnsrr to check if listed.
			// We suppress warnings because failed lookups emit them.
			if ( @checkdnsrr( $lookup_host, 'A' ) ) {
				$listings[] = array(
					'type'        => 'IP',
					'value'       => $ip,
					'rbl'         => $rbl_name,
					'rbl_suffix'  => $rbl_suffix,
					'impact'      => __( 'Email from this IP will be rejected or marked as spam', 'wpshadow' ),
				);
			}
		}

		return $listings;
	}

	/**
	 * Check domain against RBLs.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to check.
	 * @return array Array of listings, empty if not listed.
	 */
	private static function check_rbl_domain( string $domain ) : array {
		$listings = array();

		// Domain RBLs (different from IP RBLs).
		$domain_rbls = array(
			'email-black-list.anti-spam.org.uk' => 'EBL (Email Black List)',
		);

		foreach ( $domain_rbls as $rbl_suffix => $rbl_name ) {
			$lookup_host = $domain . '.' . $rbl_suffix;

			// Check if listed.
			if ( @checkdnsrr( $lookup_host, 'A' ) ) {
				$listings[] = array(
					'type'        => 'Domain',
					'value'       => $domain,
					'rbl'         => $rbl_name,
					'rbl_suffix'  => $rbl_suffix,
					'impact'      => __( 'Email from this domain will be rejected or marked as spam', 'wpshadow' ),
				);
			}
		}

		return $listings;
	}

	/**
	 * Reverse IP octets for RBL lookup.
	 *
	 * Example: 192.168.1.1 ->1.0.192
	 *
	 * @since 1.6093.1200
	 * @param  string $ip IP address to reverse.
	 * @return string|null Reversed IP or null on invalid.
	 */
	private static function reverse_ip_octets( string $ip ) {
		// Only support IPv4 for RBL lookups.
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return null;
		}

		$octets = explode( '.', $ip );
		if ( 4 !== count( $octets ) ) {
			return null;
		}

		return implode( '.', array_reverse( $octets ) );
	}

	/**
	 * Determine severity based on number of listings.
	 *
	 * @since 1.6093.1200
	 * @param  array $listings Array of RBL listings.
	 * @return string Severity level.
	 */
	private static function determine_severity( array $listings ) : string {
		$count = count( $listings );

		// Multiple listings = critical.
		if ( $count >= 3 ) {
			return 'critical';
		}

		// One or two listings = high.
		if ( $count >= 1 ) {
			return 'high';
		}

		return 'medium';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * @since 1.6093.1200
	 * @param  array $listings Array of RBL listings.
	 * @return int Threat level.
	 */
	private static function calculate_threat_level( array $listings ) : int {
		$count = count( $listings );

		// Each listing is ~25 threat points.
		$threat_level = min( $count * 25, 100 );

		return $threat_level;
	}

	/**
	 * Build user-friendly description.
	 *
	 * @since 1.6093.1200
	 * @param  string $ip Server IP.
	 * @param  string $domain Site domain.
	 * @param  array  $listings RBL listings.
	 * @return string Description text.
	 */
	private static function build_description( string $ip, string $domain, array $listings ) : string {
		$count = count( $listings );

		// What we found.
		$description = sprintf(
			/* translators: %d is the number of blacklists */
			_n(
				'Your site is listed on %d email blacklist.',
				'Your site is listed on %d email blacklists.',
				$count,
				'wpshadow'
			),
			$count
		);

		$description .= ' ';

		// Why it matters.
		$description .= __(
			'Email blacklists (RBLs) are maintained by major email providers to prevent spam. If your IP or domain is listed, your legitimate email will be rejected or sent to spam folders—even password reset emails.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the blacklists.
		$description .= __( 'Current listings:', 'wpshadow' ) . "\n";
		foreach ( $listings as $listing ) {
			$description .= sprintf(
				'• %s - Listed on %s',
				esc_html( $listing['value'] ),
				esc_html( $listing['rbl'] )
			) . "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'How to delist:', 'wpshadow' ) . "\n";
		$description .= __( '1. Visit each RBL website listed above', 'wpshadow' ) . "\n";
		$description .= __( '2. Look for "delist request" or "lookup" tool', 'wpshadow' ) . "\n";
		$description .= __( '3. Enter your IP/domain and submit request', 'wpshadow' ) . "\n";
		$description .= __( '4. Usually delisted within 24-48 hours', 'wpshadow' ) . "\n";

		return $description;
	}
}
