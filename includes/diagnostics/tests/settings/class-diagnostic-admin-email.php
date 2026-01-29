<?php
/**
 * Admin Email Configuration Diagnostic
 *
 * Verifies admin email is valid, not generic, and capable
 * of receiving security alerts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Email Configuration Class
 *
 * Validates admin email configuration is secure and functional.
 * Generic or invalid admin emails pose security risks.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Admin_Email extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates admin email is secure and functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates admin email using get_option() and is_email().
	 * Checks for generic addresses, MX records, and validity.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if email issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_admin_email_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get admin email using WordPress API (NO $wpdb).
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email is not configured. Security alerts cannot be delivered.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/settings-admin-email',
				'data'         => array(
					'admin_email' => '',
					'is_valid'    => false,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		$issues = array();

		// Validate email format.
		if ( ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email has invalid format', 'wpshadow' );
		}

		// Check for generic/example emails.
		$generic_patterns = array(
			'admin@example.com',
			'admin@localhost',
			'test@example.com',
			'webmaster@example.com',
			'noreply@',
			'no-reply@',
		);

		foreach ( $generic_patterns as $pattern ) {
			if ( stripos( $admin_email, $pattern ) !== false ) {
				$issues[] = __( 'Admin email appears to be generic or placeholder', 'wpshadow' );
				break;
			}
		}

		// Check if email domain matches site domain (potential issue).
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$email_domain = substr( strrchr( $admin_email, '@' ), 1 );

		if ( $site_domain && $email_domain !== $site_domain && strpos( $email_domain, $site_domain ) === false ) {
			// This is actually good - external email is preferred.
			// Don't flag this as an issue.
		}

		// Check MX records for email domain.
		if ( function_exists( 'checkdnsrr' ) && ! empty( $email_domain ) ) {
			$mx_exists = checkdnsrr( $email_domain, 'MX' );
			if ( ! $mx_exists ) {
				$issues[] = sprintf(
					/* translators: %s: email domain */
					__( 'No MX records found for email domain: %s', 'wpshadow' ),
					$email_domain
				);
			}
		}

		// Check if email is shared/role account.
		$role_prefixes = array( 'admin@', 'info@', 'contact@', 'support@', 'help@' );
		foreach ( $role_prefixes as $prefix ) {
			if ( 0 === stripos( $admin_email, $prefix ) ) {
				$issues[] = __( 'Admin email is a shared/role account (not ideal for security)', 'wpshadow' );
				break;
			}
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 30;
			if ( count( $issues ) >= 2 ) {
				$threat_level = 45;
			}
			if ( in_array( __( 'Admin email has invalid format', 'wpshadow' ), $issues, true ) ) {
				$threat_level = 55;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Admin email configuration has %d issues. Security alerts may not be delivered reliably.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $threat_level > 50 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/settings-admin-email',
				'data'         => array(
					'admin_email'  => $admin_email,
					'email_domain' => $email_domain,
					'issues'       => $issues,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
