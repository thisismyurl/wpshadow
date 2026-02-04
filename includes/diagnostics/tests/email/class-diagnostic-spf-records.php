<?php
/**
 * SPF Records Diagnostic
 *
 * Checks if SPF (Sender Policy Framework) records are properly configured.
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
 * SPF Records Diagnostic Class
 *
 * Verifies that SPF records are configured to authorize your mail servers.
 * SPF helps prevent your emails from being marked as spam by proving you're
 * authorized to send email from your domain.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Spf_Records extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'spf-records';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SPF Records';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SPF records are properly configured for email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the SPF records diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if SPF issues detected, null otherwise.
	 */
	public static function check() {
		// Get the site domain.
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( empty( $domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to determine site domain for SPF check', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spf-records',
			);
		}

		// Check for SPF record via DNS lookup.
		// Note: This requires DNS functions which may not be available on all hosts.
		if ( ! function_exists( 'dns_get_record' ) ) {
			return array(
				'id'           => self::$slug . '-unavailable',
				'title'        => __( 'SPF Check Unavailable', 'wpshadow' ),
				'description'  => __( 'Your server configuration prevents DNS lookups. To check SPF records manually, look for a TXT record starting with "v=spf1" in your domain\'s DNS settings.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spf-records',
				'context'      => array(
					'reason' => 'dns_get_record function not available',
					'domain' => $domain,
				),
			);
		}

		// Get TXT records for the domain.
		$dns_records = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( false === $dns_records || empty( $dns_records ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'Adding an SPF record helps email providers verify your emails are legitimate (like showing ID to prove you\'re authorized to send mail from %s). Without it, your emails may be rejected or sent to spam. You\'ll need to add a TXT record to your DNS settings.', 'wpshadow' ),
					$domain
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spf-records',
				'context'      => array(
					'domain'      => $domain,
					'spf_found'   => false,
					'dns_records' => 'No TXT records found',
				),
			);
		}

		// Look for SPF record in TXT records.
		$spf_record = null;
		foreach ( $dns_records as $record ) {
			if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=spf1' ) ) {
				$spf_record = $record['txt'];
				break;
			}
		}

		if ( null === $spf_record ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: domain name */
					__( 'Adding an SPF record helps email providers verify your emails are legitimate (like showing ID to prove you\'re authorized to send mail from %s). Without it, your emails may be rejected or sent to spam. You\'ll need to add a TXT record to your DNS settings.', 'wpshadow' ),
					$domain
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spf-records',
				'context'      => array(
					'domain'      => $domain,
					'spf_found'   => false,
					'txt_records_found' => count( $dns_records ),
				),
			);
		}

		// SPF record found - optionally validate it.
		$warnings = array();

		// Check for common SPF mistakes.
		if ( false === strpos( $spf_record, 'include:' ) && false === strpos( $spf_record, 'a' ) && false === strpos( $spf_record, 'mx' ) ) {
			$warnings[] = __( 'SPF record may be incomplete (no include/a/mx mechanisms)', 'wpshadow' );
		}

		// Check for -all (hard fail) vs ~all (soft fail).
		if ( false !== strpos( $spf_record, ' -all' ) ) {
			// Strict policy - good for security, but may cause issues.
			$warnings[] = __( 'SPF uses strict policy (-all) - ensure all email sources are included', 'wpshadow' );
		} elseif ( false === strpos( $spf_record, '~all' ) && false === strpos( $spf_record, ' +all' ) ) {
			$warnings[] = __( 'SPF record missing final policy directive (~all or -all)', 'wpshadow' );
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug . '-warnings',
				'title'        => __( 'SPF Record Warnings', 'wpshadow' ),
				'description'  => __( 'SPF record exists but may need refinement for optimal email delivery.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/spf-records',
				'context'      => array(
					'domain'      => $domain,
					'spf_record'  => $spf_record,
					'warnings'    => $warnings,
				),
			);
		}

		return null; // SPF configured properly.
	}
}
