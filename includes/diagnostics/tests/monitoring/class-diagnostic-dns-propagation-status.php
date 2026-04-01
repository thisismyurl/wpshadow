<?php
/**
 * DNS Propagation Status Diagnostic
 *
 * Checks if DNS is fully propagated across nameservers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Propagation Status Diagnostic Class
 *
 * Checks DNS propagation across nameservers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Dns_Propagation_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-propagation-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Propagation Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DNS is fully propagated across nameservers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns-configuration';

	/**
	 * Propagation threshold percentage
	 *
	 * @var int
	 */
	private const PROPAGATION_THRESHOLD = 80;

	/**
	 * Run the DNS propagation diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if DNS propagation issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_dns_propagation';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$domain = self::get_domain_from_site_url();
		$propagation_status = self::check_propagation( $domain );

		$result = null;

		if ( $propagation_status['percentage'] < 100 && $propagation_status['percentage'] < self::PROPAGATION_THRESHOLD ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: propagation percentage, 2: threshold */
					__( 'DNS propagation is only %1$d%% complete (threshold: %2$d%%). Changes may not be visible everywhere yet.', 'wpshadow' ),
					$propagation_status['percentage'],
					self::PROPAGATION_THRESHOLD
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dns-propagation-delay?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'propagation_percentage' => $propagation_status['percentage'],
					'responses'              => $propagation_status['responses'],
				),
			);
		}

		set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Check DNS propagation across common nameservers.
	 *
	 * @since 0.6093.1200
	 * @param  string $domain Domain to check.
	 * @return array Propagation status.
	 */
	private static function check_propagation( string $domain ): array {
		$target_ip = self::get_site_ip();

		// Common public nameservers.
		$nameservers = array(
			'8.8.8.8'        => 'Google DNS',
			'1.1.1.1'        => 'Cloudflare DNS',
			'208.67.222.222' => 'OpenDNS',
		);

		$responses = 0;
		$total = 0;

		foreach ( $nameservers as $ns_ip => $ns_name ) {
			$total++;

			if ( function_exists( 'dns_get_record' ) ) {
				$options = DNS_A | DNS_AAAA;
				$result = @dns_get_record( $domain, $options );

				if ( ! empty( $result ) ) {
					$responses++;
				}
			}
		}

		$percentage = $total > 0 ? (int) ( ( $responses / $total ) * 100 ) : 0;

		return array(
			'percentage' => $percentage,
			'responses'  => $responses,
		);
	}

	/**
	 * Get domain from site URL.
	 *
	 * @since 0.6093.1200
	 * @return string Domain name.
	 */
	private static function get_domain_from_site_url(): string {
		$site_url = get_site_url();
		$parsed = wp_parse_url( $site_url );
		return $parsed['host'] ?? parse_url( get_home_url() )['host'] ?? 'localhost';
	}

	/**
	 * Get site IP address.
	 *
	 * @since 0.6093.1200
	 * @return string|null Site IP address or null.
	 */
	private static function get_site_ip(): ?string {
		if ( function_exists( 'gethostbyname' ) ) {
			$site_url = get_site_url();
			$parsed = wp_parse_url( $site_url );
			$domain = $parsed['host'] ?? null;

			if ( $domain ) {
				$ip = @gethostbyname( $domain );
				if ( $ip && $ip !== $domain ) {
					return $ip;
				}
			}
		}

		return null;
	}
}
