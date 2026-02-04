<?php
/**
 * Subdomain DNS Configuration Diagnostic
 *
 * Checks if subdomains have proper DNS records.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1544
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subdomain DNS Configuration Diagnostic Class
 *
 * Checks DNS configuration for subdomains.
 *
 * @since 1.6035.1544
 */
class Diagnostic_Subdomain_Dns_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'subdomain-dns-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Subdomain DNS Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if subdomains have proper DNS records';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns-configuration';

	/**
	 * Run the subdomain DNS diagnostic check.
	 *
	 * @since  1.6035.1544
	 * @return array|null Finding array if subdomain DNS issue detected, null otherwise.
	 */
	public static function check() {
		$subdomains = self::detect_configured_subdomains();

		if ( empty( $subdomains ) ) {
			return null; // No subdomains configured.
		}

		$dns_issues = array();

		foreach ( $subdomains as $subdomain ) {
			if ( ! self::check_subdomain_dns( $subdomain ) ) {
				$dns_issues[] = $subdomain;
			}
		}

		if ( ! empty( $dns_issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated subdomains */
					__( 'DNS issues detected on subdomains: %s. Verify subdomain DNS records are configured.', 'wpshadow' ),
					implode( ', ', $dns_issues )
				),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/subdomain-dns-configuration',
				'meta'        => array(
					'affected_subdomains' => $dns_issues,
				),
			);
		}

		return null;
	}

	/**
	 * Detect configured subdomains from multisite or CNAME records.
	 *
	 * @since  1.6035.1544
	 * @return array List of subdomains.
	 */
	private static function detect_configured_subdomains(): array {
		$subdomains = array();

		// Check for multisite subdomains.
		if ( is_multisite() ) {
			$blogs = get_sites();

			foreach ( $blogs as $blog ) {
				$blog_url = get_blog_option( $blog->blog_id, 'siteurl' );
				$parsed = wp_parse_url( $blog_url );
				if ( isset( $parsed['host'] ) ) {
					$subdomains[] = $parsed['host'];
				}
			}
		}

		// Check for common CDN subdomains.
		$common_subdomains = array( 'cdn.', 'static.', 'api.', 'mail.', 'www.' );
		$domain = self::get_domain_from_site_url();

		foreach ( $common_subdomains as $prefix ) {
			$subdomains[] = $prefix . $domain;
		}

		return array_unique( $subdomains );
	}

	/**
	 * Check if subdomain has DNS record.
	 *
	 * @since  1.6035.1544
	 * @param  string $subdomain Subdomain to check.
	 * @return bool True if DNS record found.
	 */
	private static function check_subdomain_dns( string $subdomain ): bool {
		if ( ! function_exists( 'dns_get_record' ) ) {
			return true; // Can't verify.
		}

		$records = @dns_get_record( $subdomain, DNS_A | DNS_AAAA | DNS_CNAME );
		return ! empty( $records );
	}

	/**
	 * Get domain from site URL.
	 *
	 * @since  1.6035.1544
	 * @return string Domain name.
	 */
	private static function get_domain_from_site_url(): string {
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		return $parsed['host'] ?? parse_url( get_home_url() )['host'] ?? 'localhost';
	}
}
