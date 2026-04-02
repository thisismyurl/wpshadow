<?php
/**
 * DMARC Policy Set Diagnostic
 *
 * Checks if DMARC policy is properly set for the domain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DMARC Policy Set Diagnostic Class
 *
 * Verifies that DMARC (Domain-based Message Authentication, Reporting & Conformance) policy is set.
 *
 * @since 1.6093.1200
 */
class Diagnostic_DMARC_Policy_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dmarc-policy-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DMARC Policy Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies DMARC policy is set for email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the DMARC policy diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if DMARC issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_dmarc_check';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$site_domain = self::get_site_domain();
		$from_domain = self::get_from_email_domain();

		$domains_to_check = array( $site_domain );
		if ( $from_domain && $from_domain !== $site_domain ) {
			$domains_to_check[] = $from_domain;
		}

		$missing_domains = array();
		$weak_policies = array();

		foreach ( $domains_to_check as $domain ) {
			$dmarc_info = self::check_dmarc_policy( $domain );
			
			if ( ! $dmarc_info['exists'] ) {
				$missing_domains[] = $domain;
			} elseif ( 'none' === $dmarc_info['policy'] ) {
				$weak_policies[] = $domain;
			}
		}

		$result = null;

		if ( ! empty( $missing_domains ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of domains */
					__( 'DMARC policy is not set for: %s. This may cause emails to be marked as spam.', 'wpshadow' ),
					implode( ', ', $missing_domains )
				),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dmarc-policy',
				'meta'        => array(
					'missing_domains' => $missing_domains,
				),
			);
		} elseif ( ! empty( $weak_policies ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of domains */
					__( 'DMARC policy is set to "none" for: %s. Consider using "quarantine" or "reject" for better protection.', 'wpshadow' ),
					implode( ', ', $weak_policies )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dmarc-policy',
				'meta'        => array(
					'weak_policy_domains' => $weak_policies,
				),
			);
		}

		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Get the site's domain.
	 *
	 * @since 1.6093.1200
	 * @return string Site domain.
	 */
	private static function get_site_domain(): string {
		$url = get_site_url();
		$parsed = wp_parse_url( $url );
		return $parsed['host'] ?? '';
	}

	/**
	 * Get the "from" email address domain.
	 *
	 * @since 1.6093.1200
	 * @return string|null From email domain or null if not configured.
	 */
	private static function get_from_email_domain() {
		$from_email = get_option( 'admin_email' );

		// Check WP Mail SMTP plugin.
		$wp_mail_smtp = get_option( 'wp_mail_smtp' );
		if ( ! empty( $wp_mail_smtp['mail']['from_email'] ) ) {
			$from_email = $wp_mail_smtp['mail']['from_email'];
		}

		// Check Easy WP SMTP plugin.
		$easy_wp_smtp = get_option( 'swpsmtp_options' );
		if ( ! empty( $easy_wp_smtp['from_email_field'] ) ) {
			$from_email = $easy_wp_smtp['from_email_field'];
		}

		if ( $from_email && strpos( $from_email, '@' ) !== false ) {
			list( , $domain ) = explode( '@', $from_email, 2 );
			return $domain;
		}

		return null;
	}

	/**
	 * Check if domain has DMARC policy.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to check.
	 * @return array Array with 'exists' and 'policy' keys.
	 */
	private static function check_dmarc_policy( string $domain ): array {
		$result = array(
			'exists' => false,
			'policy' => '',
		);

		if ( ! function_exists( 'checkdnsrr' ) ) {
			$result['exists'] = true; // Can't check, assume it's okay.
			return $result;
		}

		$dmarc_domain = '_dmarc.' . $domain;
		$records = dns_get_record( $dmarc_domain, DNS_TXT );

		if ( ! empty( $records ) ) {
			foreach ( $records as $record ) {
				if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=DMARC1' ) === 0 ) {
					$result['exists'] = true;
					
					// Extract policy.
					if ( preg_match( '/p=(none|quarantine|reject)/i', $record['txt'], $matches ) ) {
						$result['policy'] = strtolower( $matches[1] );
					}
					
					break;
				}
			}
		}

		return $result;
	}
}
