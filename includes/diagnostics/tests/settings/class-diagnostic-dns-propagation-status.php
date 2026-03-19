<?php
/**
 * DNS Propagation Status Diagnostic
 *
 * Checks for basic DNS propagation consistency between root and www records.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Propagation Status Diagnostic Class
 *
 * Validates that DNS records resolve consistently across common variants.
 *
 * @since 1.6093.1200
 */
class Diagnostic_DNS_Propagation_Status extends Diagnostic_Base {

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
	protected static $description = 'Checks for consistent DNS resolution between root and www records';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$domain = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine the site domain for DNS propagation checks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-propagation-status',
			);
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DNS propagation cannot be verified because dns_get_record is unavailable.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-propagation-status',
			);
		}

		$root_records = @dns_get_record( $domain, DNS_A );
		$www_records  = @dns_get_record( 'www.' . $domain, DNS_A );

		$root_ips = self::get_record_ips( $root_records );
		$www_ips  = self::get_record_ips( $www_records );

		if ( empty( $root_ips ) || empty( $www_ips ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DNS resolution for root or www records is incomplete. This can cause intermittent access depending on the hostname.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-propagation-status',
				'meta'         => array(
					'root_ips' => $root_ips,
					'www_ips'  => $www_ips,
				),
			);
		}

		$diff = array_diff( $root_ips, $www_ips );
		if ( ! empty( $diff ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Root and www DNS records resolve to different IPs. Confirm propagation is complete and redirects are configured properly.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-propagation-status',
				'meta'         => array(
					'root_ips' => $root_ips,
					'www_ips'  => $www_ips,
				),
			);
		}

		return null;
	}

	/**
	 * Extract IPs from DNS records.
	 *
	 * @since 1.6093.1200
	 * @param  array|false $records DNS record array.
	 * @return array List of unique IPs.
	 */
	private static function get_record_ips( $records ): array {
		$ips = array();

		if ( ! is_array( $records ) ) {
			return $ips;
		}

		foreach ( $records as $record ) {
			if ( ! empty( $record['ip'] ) ) {
				$ips[] = $record['ip'];
			}
		}

		return array_values( array_unique( $ips ) );
	}
}
