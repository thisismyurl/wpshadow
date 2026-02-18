<?php
/**
 * Sender Reputation Diagnostic
 *
 * Checks for factors that could damage email sender reputation.
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
 * Sender Reputation Diagnostic Class
 *
 * Monitors factors that affect your domain's sender reputation. A poor
 * reputation causes emails to be blocked or sent to spam (like having a
 * bad credit score affecting future transactions).
 *
 * @since 1.6035.1530
 */
class Diagnostic_Sender_Reputation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sender-reputation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sender Reputation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors factors that could damage email sender reputation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the sender reputation diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if reputation issues detected, null otherwise.
	 */
	public static function check() {
		$reputation_issues = array();

		// Check 1: Domain age (new domains have lower trust).
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		// Check 2: Blacklist status (if available).
		$blacklist_check = get_transient( 'wpshadow_blacklist_status_' . md5( $domain ) );
		if ( false !== $blacklist_check && 'blacklisted' === $blacklist_check ) {
			$reputation_issues[] = __( 'Domain or IP address found on spam blacklists', 'wpshadow' );
		}

		// Check 3: Email volume patterns.
		$recent_email_volume = get_transient( 'wpshadow_email_volume_24h' );
		if ( false !== $recent_email_volume && $recent_email_volume > 1000 ) {
			// Check for sudden spike.
			$previous_volume = get_transient( 'wpshadow_email_volume_24h_previous' );
			if ( false !== $previous_volume && $recent_email_volume > ( $previous_volume * 3 ) ) {
				$reputation_issues[] = __( 'Sudden spike in email volume detected - may trigger spam filters', 'wpshadow' );
			}
		}

		// Check 4: Bounce rate.
		$bounce_rate = get_option( 'wpshadow_email_bounce_rate', 0 );
		if ( $bounce_rate > 10 ) {
			$reputation_issues[] = sprintf(
				/* translators: %s: bounce rate percentage */
				__( 'High bounce rate (%s%%) - repeatedly sending to invalid addresses damages reputation', 'wpshadow' ),
				$bounce_rate
			);
		}

		// Check 5: Spam complaint rate.
		$complaint_rate = get_option( 'wpshadow_email_complaint_rate', 0 );
		if ( $complaint_rate > 0.1 ) {
			$reputation_issues[] = sprintf(
				/* translators: %s: complaint rate percentage */
				__( 'High spam complaint rate (%s%%) - recipients marking emails as spam', 'wpshadow' ),
				$complaint_rate
			);
		}

		// Check 6: Missing authentication (SPF/DKIM/DMARC).
		$auth_missing = array();

		// Check SPF.
		if ( function_exists( 'dns_get_record' ) ) {
			$dns_records = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$has_spf = false;
			if ( ! empty( $dns_records ) ) {
				foreach ( $dns_records as $record ) {
					if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=spf1' ) ) {
						$has_spf = true;
						break;
					}
				}
			}
			if ( ! $has_spf ) {
				$auth_missing[] = 'SPF';
			}

			// Check DMARC.
			$dmarc_domain = '_dmarc.' . $domain;
			$dmarc_records = @dns_get_record( $dmarc_domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$has_dmarc = false;
			if ( ! empty( $dmarc_records ) ) {
				foreach ( $dmarc_records as $record ) {
					if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=DMARC1' ) ) {
						$has_dmarc = true;
						break;
					}
				}
			}
			if ( ! $has_dmarc ) {
				$auth_missing[] = 'DMARC';
			}
		}

		if ( ! empty( $auth_missing ) ) {
			$reputation_issues[] = sprintf(
				/* translators: %s: comma-separated list of missing authentication types */
				__( 'Missing email authentication: %s', 'wpshadow' ),
				implode( ', ', $auth_missing )
			);
		}

		// Check 7: Using shared hosting IP (harder to maintain good reputation).
		if ( function_exists( 'gethostbyname' ) ) {
			$server_ip = gethostbyname( $domain );
			if ( filter_var( $server_ip, FILTER_VALIDATE_IP ) ) {
				// Check if IP is in common shared hosting ranges (simplified check).
				$is_shared = false;
				if ( 0 === strpos( $server_ip, '192.168.' ) || 0 === strpos( $server_ip, '10.' ) ) {
					$is_shared = true;
				}

				if ( $is_shared ) {
					$reputation_issues[] = __( 'Using shared hosting IP - consider dedicated IP for better email reputation control', 'wpshadow' );
				}
			}
		}

		// If there are reputation issues, return finding.
		if ( ! empty( $reputation_issues ) ) {
			$severity = count( $reputation_issues ) > 3 ? 'high' : 'medium';
			$threat_level = count( $reputation_issues ) > 3 ? 70 : 50;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your email sender reputation could be improved (think of it like your credit score for sending emails). Issues with reputation cause emails to be blocked or sent to spam folders. The factors affecting your reputation are listed below.', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sender-reputation',
				'context'      => array(
					'domain'            => $domain,
					'issues_count'      => count( $reputation_issues ),
					'reputation_issues' => $reputation_issues,
				),
			);
		}

		return null; // Sender reputation appears healthy.
	}
}
