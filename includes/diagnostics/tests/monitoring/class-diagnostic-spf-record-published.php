<?php
/**
 * SPF Record Published Diagnostic
 *
 * Checks if SPF records are properly published for the domain.
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
 * SPF Record Published Diagnostic Class
 *
 * Verifies that SPF (Sender Policy Framework) records are published for improved email deliverability.
 *
 * @since 0.6093.1200
 */
class Diagnostic_SPF_Record_Published extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'spf-record-published';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SPF Record Published';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies SPF records are published for email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the SPF record diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if SPF issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_spf_check';
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
			if ( ! self::has_spf_record( $domain ) ) {
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
					__( 'SPF records are not published for: %s. This may cause emails to be marked as spam.', 'wpshadow' ),
					implode( ', ', $missing_domains )
				),
				'severity'    => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/spf-records?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
	 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
	 * Check if domain has SPF record.
	 *
	 * @since 0.6093.1200
	 * @param  string $domain Domain to check.
	 * @return bool True if SPF record exists, false otherwise.
	 */
	private static function has_spf_record( string $domain ): bool {
		if ( ! function_exists( 'checkdnsrr' ) ) {
			return true; // Can't check, assume it's okay.
		}

		// Check for TXT records.
		$records = dns_get_record( $domain, DNS_TXT );

		if ( ! $records ) {
			return false;
		}

		foreach ( $records as $record ) {
			if ( isset( $record['txt'] ) && strpos( $record['txt'], 'v=spf1' ) === 0 ) {
				return true;
			}
		}

		return false;
	}
}
