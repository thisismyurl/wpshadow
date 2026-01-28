<?php
/**
 * DMARC Policy Missing Diagnostic
 *
 * Validates DMARC (Domain-based Message Authentication, Reporting & Conformance)
 * policy exists to prevent email spoofing and improve deliverability.
 *
 * DMARC builds on SPF and DKIM by adding alignment checks and reporting,
 * becoming increasingly required by enterprise email systems.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Email
 * @since      1.6028.2050
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_DMARC_Policy_Missing Class
 *
 * Queries DNS for _dmarc.domain.com TXT record and validates DMARC policy.
 * Critical for email deliverability and enterprise compliance.
 *
 * @since 1.6028.2050
 */
class Diagnostic_DMARC_Policy_Missing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dmarc-policy-missing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DMARC Policy Missing';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates DMARC policy exists to prevent email spoofing';

	/**
	 * Diagnostic family/category
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the DMARC policy diagnostic check.
	 *
	 * Queries DNS for _dmarc.domain.com TXT record, validates syntax,
	 * and checks for proper policy configuration (p=quarantine or p=reject).
	 *
	 * @since  1.6028.2050
	 * @return array|null Finding array if DMARC issue detected, null if properly configured.
	 */
	public static function check() {
		$domain = self::get_site_domain();

		if ( ! $domain ) {
			return null; // Cannot determine domain.
		}

		$dmarc_data = self::check_dmarc_record( $domain );

		if ( ! $dmarc_data['exists'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'No DMARC record found for domain %s. DMARC prevents email spoofing and is increasingly required by email providers.', 'wpshadow' ),
					esc_html( $domain )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'solution'     => array(
					'free'     => array(
						'heading'     => __( 'Create DMARC DNS Record', 'wpshadow' ),
						'description' => sprintf(
							/* translators: %s: domain name */
							__( 'Add TXT record at _dmarc.%s with policy v=DMARC1; p=quarantine; rua=mailto:dmarc@%s', 'wpshadow' ),
							esc_html( $domain ),
							esc_html( $domain )
						),
						'steps'       => array(
							__( 'Access your domain DNS settings', 'wpshadow' ),
							__( 'Add TXT record: _dmarc.yourdomain.com', 'wpshadow' ),
							__( 'Value: v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com', 'wpshadow' ),
							__( 'Wait 24-48 hours for DNS propagation', 'wpshadow' ),
							__( 'Test with MXToolbox DMARC Lookup', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'heading'     => __( 'Email Authentication Setup Service', 'wpshadow' ),
						'description' => __( 'Professional DMARC, SPF, and DKIM configuration with monitoring and reporting.', 'wpshadow' ),
					),
					'advanced' => array(
						'heading'     => __( 'DMARC Reporting & Analysis', 'wpshadow' ),
						'description' => __( 'Use services like Postmark DMARC Digests or DMARC Analyzer for aggregate report analysis and policy refinement.', 'wpshadow' ),
					),
				),
				'details'      => array(
					'domain'       => $domain,
					'dmarc_lookup' => '_dmarc.' . $domain,
					'record_found' => false,
				),
				'resource_links' => array(
					array(
						'title' => __( 'DMARC Setup Guide', 'wpshadow' ),
						'url'   => 'https://dmarc.org/overview/',
					),
					array(
						'title' => __( 'MXToolbox DMARC Check', 'wpshadow' ),
						'url'   => 'https://mxtoolbox.com/DMARC.aspx',
					),
				),
				'kb_link'      => 'https://wpshadow.com/kb/dmarc-policy-configuration',
			);
		}

		// DMARC exists but check policy strength.
		if ( $dmarc_data['policy'] === 'none' ) {
			return array(
				'id'           => self::$slug,
				'title'        => __( 'DMARC Policy Too Permissive', 'wpshadow' ),
				'description'  => __( 'DMARC policy is set to "none" (monitoring only). Upgrade to "quarantine" or "reject" for active protection.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'solution'     => array(
					'free'     => array(
						'heading'     => __( 'Upgrade DMARC Policy', 'wpshadow' ),
						'description' => __( 'Change p=none to p=quarantine after monitoring for 30 days, then to p=reject after another 30 days.', 'wpshadow' ),
						'steps'       => array(
							__( 'Monitor DMARC reports for 30 days at p=none', 'wpshadow' ),
							__( 'Update TXT record to p=quarantine', 'wpshadow' ),
							__( 'Monitor for false positives for 30 days', 'wpshadow' ),
							__( 'Upgrade to p=reject for maximum security', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'heading'     => __( 'Gradual DMARC Rollout', 'wpshadow' ),
						'description' => __( 'Use pct=10 to apply policy to 10% of emails, gradually increasing to 100%.', 'wpshadow' ),
					),
					'advanced' => array(
						'heading'     => __( 'Subdomain DMARC Policies', 'wpshadow' ),
						'description' => __( 'Configure separate policies for subdomains using sp= tag for granular control.', 'wpshadow' ),
					),
				),
				'details'      => array(
					'domain'       => $domain,
					'dmarc_record' => $dmarc_data['record'],
					'policy'       => $dmarc_data['policy'],
					'alignment'    => $dmarc_data['alignment'],
					'reporting'    => $dmarc_data['reporting'],
				),
				'resource_links' => array(
					array(
						'title' => __( 'DMARC Policy Levels', 'wpshadow' ),
						'url'   => 'https://dmarc.org/overview/',
					),
				),
				'kb_link'      => 'https://wpshadow.com/kb/dmarc-policy-upgrade',
			);
		}

		return null; // DMARC configured properly.
	}

	/**
	 * Get site domain from WordPress home URL.
	 *
	 * @since  1.6028.2050
	 * @return string Site domain or empty string.
	 */
	private static function get_site_domain() {
		$home_url = home_url();
		$parsed   = wp_parse_url( $home_url );

		if ( ! isset( $parsed['host'] ) ) {
			return '';
		}

		$host = $parsed['host'];

		// Remove www prefix for DNS lookups.
		if ( 0 === strpos( $host, 'www.' ) ) {
			$host = substr( $host, 4 );
		}

		return $host;
	}

	/**
	 * Check DMARC DNS record for domain.
	 *
	 * Queries _dmarc.domain.com TXT record and parses policy.
	 *
	 * @since  1.6028.2050
	 * @param  string $domain Domain to check.
	 * @return array {
	 *     DMARC record data.
	 *
	 *     @type bool   $exists    Whether DMARC record exists.
	 *     @type string $record    Full DMARC TXT record.
	 *     @type string $policy    Policy value (none|quarantine|reject).
	 *     @type array  $alignment SPF/DKIM alignment settings.
	 *     @type array  $reporting Reporting email addresses.
	 * }
	 */
	private static function check_dmarc_record( $domain ) {
		$dmarc_host = '_dmarc.' . $domain;

		// Query DNS for TXT record.
		$records = dns_get_record( $dmarc_host, DNS_TXT ); // phpcs:ignore WordPress.WP.AlternativeFunctions.dns_get_record_dns_get_record

		if ( ! $records ) {
			return array(
				'exists'    => false,
				'record'    => '',
				'policy'    => 'none',
				'alignment' => array(),
				'reporting' => array(),
			);
		}

		// Find DMARC record (starts with v=DMARC1).
		$dmarc_record = '';
		foreach ( $records as $record ) {
			if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=DMARC1' ) ) {
				$dmarc_record = $record['txt'];
				break;
			}
		}

		if ( empty( $dmarc_record ) ) {
			return array(
				'exists'    => false,
				'record'    => '',
				'policy'    => 'none',
				'alignment' => array(),
				'reporting' => array(),
			);
		}

		// Parse DMARC record.
		$policy_match = array();
		preg_match( '/p=(none|quarantine|reject)/', $dmarc_record, $policy_match );
		$policy = isset( $policy_match[1] ) ? $policy_match[1] : 'none';

		// Extract alignment settings.
		$aspf_match = array();
		preg_match( '/aspf=(r|s)/', $dmarc_record, $aspf_match );
		$aspf = isset( $aspf_match[1] ) ? $aspf_match[1] : 'r'; // Default relaxed.

		$adkim_match = array();
		preg_match( '/adkim=(r|s)/', $dmarc_record, $adkim_match );
		$adkim = isset( $adkim_match[1] ) ? $adkim_match[1] : 'r'; // Default relaxed.

		// Extract reporting emails.
		$rua_match = array();
		preg_match( '/rua=([^;]+)/', $dmarc_record, $rua_match );
		$rua = isset( $rua_match[1] ) ? $rua_match[1] : '';

		$ruf_match = array();
		preg_match( '/ruf=([^;]+)/', $dmarc_record, $ruf_match );
		$ruf = isset( $ruf_match[1] ) ? $ruf_match[1] : '';

		return array(
			'exists'    => true,
			'record'    => $dmarc_record,
			'policy'    => $policy,
			'alignment' => array(
				'spf'  => $aspf,
				'dkim' => $adkim,
			),
			'reporting' => array(
				'aggregate' => $rua,
				'forensic'  => $ruf,
			),
		);
	}
}
