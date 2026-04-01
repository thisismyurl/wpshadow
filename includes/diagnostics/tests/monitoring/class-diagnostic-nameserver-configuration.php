<?php
/**
 * Nameserver Configuration Diagnostic
 *
 * Checks if correct nameservers are configured.
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
 * Nameserver Configuration Diagnostic Class
 *
 * Verifies correct nameservers are configured for domain.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Nameserver_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'nameserver-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Nameserver Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if correct nameservers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'dns-configuration';

	/**
	 * Run the nameserver configuration diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if nameserver issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_nameserver_config';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$domain = self::get_domain_from_site_url();
		$nameservers = self::get_nameservers( $domain );

		$result = null;

		if ( empty( $nameservers ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: domain */
					__( 'Could not retrieve nameservers for %s. Verify domain DNS settings.', 'wpshadow' ),
					$domain
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/nameserver-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'domain' => $domain,
				),
			);
		} elseif ( count( $nameservers ) < 2 ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: nameserver count */
					__( 'Only %d nameserver(s) found. Recommended: at least 2 for redundancy.', 'wpshadow' ),
					count( $nameservers )
				),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/nameserver-redundancy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'nameservers' => $nameservers,
				),
			);
		}

		set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Get nameservers for domain.
	 *
	 * @since 0.6093.1200
	 * @param  string $domain Domain to check.
	 * @return array List of nameservers.
	 */
	private static function get_nameservers( string $domain ): array {
		$nameservers = array();

		if ( function_exists( 'dns_get_record' ) ) {
			$ns_records = @dns_get_record( $domain, DNS_NS );

			if ( $ns_records && is_array( $ns_records ) ) {
				foreach ( $ns_records as $record ) {
					if ( isset( $record['target'] ) ) {
						$nameservers[] = $record['target'];
					}
				}
			}
		}

		return array_unique( $nameservers );
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
}
