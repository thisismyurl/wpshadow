<?php
/**
 * DNS Records Validation Diagnostic
 *
 * Checks if essential DNS records are configured correctly.
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
 * DNS Records Validation Diagnostic Class
 *
 * Validates DNS records for domain.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Dns_Records_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-records-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Records Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if essential DNS records are configured correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns-configuration';

	/**
	 * Run the DNS validation diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if DNS issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_dns_validation';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$domain = self::get_domain_from_site_url();
		$dns_issues = array();

		// Check A record.
		if ( ! self::check_a_record( $domain ) ) {
			$dns_issues[] = 'A record';
		}

		// Check MX record.
		if ( ! self::check_mx_record( $domain ) ) {
			$dns_issues[] = 'MX record';
		}

		$result = null;

		if ( ! empty( $dns_issues ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated DNS records */
					__( 'DNS issue(s) detected: %s missing or misconfigured. Verify DNS settings with your hosting provider.', 'wpshadow' ),
					implode( ', ', $dns_issues )
				),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dns-record-configuration',
				'meta'        => array(
					'domain'       => $domain,
					'issues'       => $dns_issues,
				),
			);
		}

		set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Get domain from site URL.
	 *
	 * @since 1.6093.1200
	 * @return string Domain name.
	 */
	private static function get_domain_from_site_url(): string {
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		return $parsed['host'] ?? parse_url( get_home_url() )['host'] ?? 'localhost';
	}

	/**
	 * Check if A record exists.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to check.
	 * @return bool True if A record found.
	 */
	private static function check_a_record( string $domain ): bool {
		if ( ! function_exists( 'dns_get_record' ) ) {
			return true; // Can't verify.
		}

		$records = @dns_get_record( $domain, DNS_A );
		return ! empty( $records );
	}

	/**
	 * Check if MX record exists.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to check.
	 * @return bool True if MX record found.
	 */
	private static function check_mx_record( string $domain ): bool {
		if ( ! function_exists( 'dns_get_mx_record' ) ) {
			return true; // Can't verify.
		}

		$mxhosts = array();
		$result = @dns_get_mx_record( $domain, $mxhosts );
		return $result && ! empty( $mxhosts );
	}
}
