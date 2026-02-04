<?php
/**
 * DNS A Record Validation Diagnostic
 *
 * Validates that the DNS A record for the site points to the expected server IP.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS A Record Validation Diagnostic Class
 *
 * Checks A records for the site domain and compares against the server address.
 *
 * @since 1.6035.0900
 */
class Diagnostic_DNS_A_Record_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-a-record-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS A Record Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that the DNS A record points to the correct server IP';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$domain = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine the site domain for DNS validation. Confirm your Site Address URL is set correctly.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-record-validation',
			);
		}

		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DNS validation cannot run because dns_get_record is unavailable. Enable the PHP DNS functions to verify records.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-record-validation',
			);
		}

		$records = @dns_get_record( $domain, DNS_A );
		if ( empty( $records ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No DNS A records were found for your domain. This can prevent the site from resolving correctly.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-record-validation',
				'meta'         => array(
					'domain' => $domain,
				),
			);
		}

		$record_ips = array();
		foreach ( $records as $record ) {
			if ( ! empty( $record['ip'] ) ) {
				$record_ips[] = $record['ip'];
			}
		}

		$record_ips = array_unique( $record_ips );
		$server_ip  = $_SERVER['SERVER_ADDR'] ?? '';
		$host_ip    = gethostbyname( $domain );

		if ( $server_ip && ! in_array( $server_ip, $record_ips, true ) && $host_ip && ! in_array( $host_ip, $record_ips, true ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'DNS A records do not match the current server IP. This can cause traffic to route to the wrong host.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dns-a-record-validation',
				'meta'         => array(
					'domain'     => $domain,
					'record_ips' => $record_ips,
					'server_ip'  => $server_ip,
					'host_ip'    => $host_ip,
				),
			);
		}

		return null;
	}
}
