<?php
/**
 * DNS CAA Records Not Configured Diagnostic
 *
 * Checks if DNS CAA records are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS CAA Records Not Configured Diagnostic Class
 *
 * Detects missing DNS CAA records.
 *
 * @since 1.2601.2352
 */
class Diagnostic_DNS_CAA_Records_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-caa-records-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS CAA Records Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DNS CAA records are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CAA DNS records using DNS query
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $domain && ! get_option( 'dns_caa_records_checked' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'DNS CAA records are not configured. Add CAA records to restrict which Certificate Authorities can issue SSL certificates for your domain to prevent unauthorized certificate issuance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dns-caa-records-not-configured',
			);
		}

		return null;
	}
}
