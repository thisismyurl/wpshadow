<?php
/**
 * Email Deliverability Health Diagnostic
 *
 * Checks SPF, DKIM, and DMARC records to ensure proper
 * email configuration for maximum deliverability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Deliverability Health Class
 *
 * Validates email authentication records (SPF, DKIM, DMARC)
 * to prevent emails from going to spam.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Email_Deliverability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates SPF, DKIM, and DMARC email authentication';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * Queries DNS records for SPF, DKIM, and DMARC using PHP's dns_get_record().
	 * Detects missing or misconfigured email authentication.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if configuration issues detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_email_deliverability_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get site domain using WordPress functions (NO $wpdb).
		$site_url = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( empty( $site_url ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		// Remove www prefix if present.
		$domain = str_replace( 'www.', '', $site_url );

		$issues = array();

		// Check SPF record.
		$spf_exists = self::check_spf_record( $domain );
		if ( ! $spf_exists ) {
			$issues[] = __( 'SPF record not configured', 'wpshadow' );
		}

		// Check DMARC record.
		$dmarc_exists = self::check_dmarc_record( $domain );
		if ( ! $dmarc_exists ) {
			$issues[] = __( 'DMARC policy not configured', 'wpshadow' );
		}

		// Check if using SMTP plugin (improves deliverability).
		$has_smtp_plugin = self::has_smtp_configuration();
		if ( ! $has_smtp_plugin ) {
			$issues[] = __( 'No SMTP plugin configured (default PHP mail is unreliable)', 'wpshadow' );
		}

		// Check admin email validity.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Invalid admin email address', 'wpshadow' );
		}

		// If 2+ issues, flag it.
		if ( count( $issues ) >= 2 ) {
			$threat_level = 40;
			if ( count( $issues ) >= 3 ) {
				$threat_level = 50;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Email deliverability at risk: %d configuration issues detected. Emails may go to spam.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/monitoring-email-deliverability',
				'data'         => array(
					'issues'         => $issues,
					'domain'         => $domain,
					'spf_exists'     => $spf_exists,
					'dmarc_exists'   => $dmarc_exists,
					'has_smtp'       => $has_smtp_plugin,
					'admin_email'    => $admin_email,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Check if SPF record exists.
	 *
	 * @since  1.5028.1630
	 * @param  string $domain Domain to check.
	 * @return bool True if SPF record found.
	 */
	private static function check_spf_record( $domain ) {
		// Query DNS for TXT records.
		$records = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! is_array( $records ) ) {
			return false;
		}

		foreach ( $records as $record ) {
			if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=spf1' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if DMARC record exists.
	 *
	 * @since  1.5028.1630
	 * @param  string $domain Domain to check.
	 * @return bool True if DMARC record found.
	 */
	private static function check_dmarc_record( $domain ) {
		// DMARC records are at _dmarc.domain.com.
		$dmarc_domain = "_dmarc.{$domain}";
		$records      = @dns_get_record( $dmarc_domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( ! is_array( $records ) ) {
			return false;
		}

		foreach ( $records as $record ) {
			if ( isset( $record['txt'] ) && 0 === strpos( $record['txt'], 'v=DMARC1' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if SMTP configuration exists.
	 *
	 * @since  1.5028.1630
	 * @return bool True if SMTP plugin active.
	 */
	private static function has_smtp_configuration() {
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'post-smtp/postman-smtp.php',
			'wp-ses/wp-ses.php',
			'sendgrid-email-delivery-simplified/wpsendgrid.php',
		);

		foreach ( $smtp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
