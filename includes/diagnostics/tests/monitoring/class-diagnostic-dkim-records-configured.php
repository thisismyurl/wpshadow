<?php
/**
 * DKIM Records Configured Diagnostic
 *
 * Checks if DKIM records are properly configured for the domain.
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
 * DKIM Records Configured Diagnostic Class
 *
 * Verifies that DKIM (DomainKeys Identified Mail) records are configured for email authentication.
 *
 * @since 1.6093.1200
 */
class Diagnostic_DKIM_Records_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dkim-records-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DKIM Records Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies DKIM records are configured for email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the DKIM records diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if DKIM issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_dkim_check';
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

		foreach ( $domains_to_check as $domain ) {
			if ( ! self::has_dkim_record( $domain ) ) {
				$missing_domains[] = $domain;
			}
		}

		$result = null;

		if ( ! empty( $missing_domains ) ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of domains */
					__( 'DKIM records are not configured for: %s. This may cause emails to be marked as spam.', 'wpshadow' ),
					implode( ', ', $missing_domains )
				),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dkim-configuration',
				'meta'        => array(
					'missing_domains' => $missing_domains,
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
	 * Check if domain has DKIM record.
	 *
	 * Checks common DKIM selectors: default, mail, smtp, k1.
	 *
	 * @since 1.6093.1200
	 * @param  string $domain Domain to check.
	 * @return bool True if DKIM record exists, false otherwise.
	 */
	private static function has_dkim_record( string $domain ): bool {
		if ( ! function_exists( 'checkdnsrr' ) ) {
			return true; // Can't check, assume it's okay.
		}

		$common_selectors = array( 'default', 'mail', 'smtp', 'k1', 'google', 'selector1', 'selector2' );

		foreach ( $common_selectors as $selector ) {
			$dkim_domain = $selector . '._domainkey.' . $domain;
			$records = dns_get_record( $dkim_domain, DNS_TXT );

			if ( ! empty( $records ) ) {
				foreach ( $records as $record ) {
					if ( isset( $record['txt'] ) && ( strpos( $record['txt'], 'v=DKIM1' ) === 0 || strpos( $record['txt'], 'k=rsa' ) !== false ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}
