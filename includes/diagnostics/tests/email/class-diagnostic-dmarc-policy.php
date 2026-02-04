<?php
/**
 * DMARC Policy Diagnostic
 *
 * Checks if DMARC (Domain-based Message Authentication) policy is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DMARC Policy Diagnostic Class
 *
 * Verifies that DMARC policy is configured. DMARC tells email providers what
 * to do with emails that fail SPF/DKIM checks (like a bouncer at a club with
 * instructions for handling fake IDs).
 *
 * @since 1.6035.1530
 */
class Diagnostic_Dmarc_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dmarc-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DMARC Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DMARC policy is configured for email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the DMARC policy diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if DMARC issues detected, null otherwise.
	 */
	public static function check() {
		// Get the site domain.
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( empty( $domain ) ) {
			return null;
		}

		// Check for DMARC record via DNS lookup.
		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug . '-unavailable',
				'title'        => __( 'DMARC Check Unavailable', 'wpshadow' ),
				'description'  => __( 'Your server configuration prevents DNS lookups. To check DMARC records manually, look for a TXT record at _dmarc.yourdomain.com starting with "v=DMARC1".', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dmarc-policy',
			);
		}

		// Check _dmarc subdomain for DMARC record.
		$dmarc_domain = '_dmarc.' . $domain;
		$dns_records  = @dns_get_record( $dmarc_domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$dmarc_record = null;
		if ( ! empty( $dns_records ) ) {
			foreach ( $dns_records as $record ) {
				if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=DMARC1' ) ) {
					$dmarc_record = $record['txt'];
					break;
				}
			}
		}

		if ( null === $dmarc_record ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding a DMARC policy tells email providers what to do with suspicious emails claiming to be from your domain (like giving a bouncer instructions: "If someone shows a fake ID with my name, reject them"). This protects your domain from being used for spam or phishing.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dmarc-policy',
				'context'      => array(
					'domain'       => $domain,
					'dmarc_domain' => $dmarc_domain,
					'dmarc_found'  => false,
				),
			);
		}

		// DMARC record found - check policy strictness.
		$warnings = array();

		// Check policy directive (p=).
		if ( false !== strpos( $dmarc_record, 'p=none' ) ) {
			$warnings[] = __( 'DMARC policy is "none" (monitor only) - consider "quarantine" or "reject" for better protection', 'wpshadow' );
		}

		// Check for reporting email.
		if ( false === strpos( $dmarc_record, 'rua=' ) ) {
			$warnings[] = __( 'DMARC record missing aggregate reports email (rua=) - you won\'t receive delivery reports', 'wpshadow' );
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug . '-warnings',
				'title'        => __( 'DMARC Policy Warnings', 'wpshadow' ),
				'description'  => __( 'DMARC policy exists but could be strengthened for better email security.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dmarc-policy',
				'context'      => array(
					'domain'       => $domain,
					'dmarc_record' => $dmarc_record,
					'warnings'     => $warnings,
				),
			);
		}

		return null; // DMARC configured properly.
	}
}
