<?php
/**
 * DNS Health Check Diagnostic
 *
 * Checks if DNS records are properly configured and healthy.
 *
 * @package WPShadow\Diagnostics
 * @since   1.6032.0146
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: DNS Health Check
 *
 * Detects DNS configuration issues and validates record health.
 */
class Diagnostic_DNS_Health_Check extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-health-check';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Health Check';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates DNS records and configuration health';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'dns';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$home_url = home_url();
		$host     = wp_parse_url( $home_url, PHP_URL_HOST );
		$stats['domain'] = $host;

		// Check DNS resolution
		$dns_records = dns_get_record( $host );
		$stats['dns_records_found'] = is_array( $dns_records ) ? count( $dns_records ) : 0;

		// Check for MX records (email)
		$mx_records = dns_get_mx( $host );
		$stats['mx_records_found'] = is_array( $mx_records ) ? count( $mx_records ) : 0;

		// Check for SPF record (email security)
		if ( is_array( $dns_records ) ) {
			$spf_found = false;
			foreach ( $dns_records as $record ) {
				if ( 'TXT' === $record['type'] && preg_match( '/v=spf1/', $record['data'] ?? '' ) ) {
					$spf_found = true;
					break;
				}
			}
			$stats['spf_record_found'] = $spf_found;
		}

		if ( empty( $dns_records ) ) {
			$issues[] = __( 'DNS records could not be retrieved', 'wpshadow' );
		}

		if ( ! $stats['mx_records_found'] ) {
			$issues[] = __( 'No MX records found for email routing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Properly configured DNS records ensure your domain resolves correctly, email is delivered reliably, and your site is accessible worldwide. Issues with DNS affect site accessibility and email deliverability.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dns-setup',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
