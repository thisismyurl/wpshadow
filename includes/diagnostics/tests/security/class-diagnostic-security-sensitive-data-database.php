<?php
/**
 * Diagnostic: Sensitive Data in Database
 *
 * Checks for plaintext passwords, credit card data, and API keys stored insecurely
 * in the WordPress database. Critical security risk (OWASP A02:2021).
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4007
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensitive Data in Database Diagnostic
 *
 * Detects sensitive information stored in plaintext within the database.
 * Checks for passwords, credit cards, API keys in wp_usermeta and wp_options.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Security_Sensitive_Data_Database extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-sensitive-data-database';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Data in Database';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plaintext passwords, credit card data, and API keys in database';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check for sensitive data in database.
	 *
	 * Scans wp_usermeta and wp_options for patterns matching:
	 * - Plaintext passwords
	 * - Credit card numbers
	 * - API keys (various services)
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check wp_usermeta for suspicious password-related keys.
		$suspicious_meta_keys = array(
			'password',
			'passwd',
			'pwd',
			'pass',
			'user_pass',
			'plaintext_password',
		);

		foreach ( $suspicious_meta_keys as $meta_key ) {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->usermeta}
					WHERE meta_key LIKE %s",
					'%' . $wpdb->esc_like( $meta_key ) . '%'
				)
			);

			if ( $count > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: meta key pattern, 2: count */
					__( 'Found %2$d usermeta entries matching "%1$s"', 'wpshadow' ),
					$meta_key,
					$count
				);
			}
		}

		// Check wp_options for API keys in plaintext.
		$api_key_patterns = array(
			'api_key',
			'api_secret',
			'secret_key',
			'private_key',
			'access_token',
			'auth_token',
		);

		foreach ( $api_key_patterns as $pattern ) {
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->options}
					WHERE option_name LIKE %s
					AND option_value != ''
					AND option_value NOT LIKE '%%encrypted%%'
					AND option_value NOT LIKE '%%hashed%%'",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);

			if ( $count > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: pattern, 2: count */
					__( 'Found %2$d options with "%1$s" potentially stored in plaintext', 'wpshadow' ),
					$pattern,
					$count
				);
			}
		}

		// Check for credit card patterns (basic regex).
		// Look for 16-digit sequences in option values (simplified check).
		$cc_check = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_value REGEXP '[0-9]{4}[- ]?[0-9]{4}[- ]?[0-9]{4}[- ]?[0-9]{4}'"
		);

		if ( $cc_check > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count of suspicious entries */
				__( 'Found %d options containing potential credit card number patterns', 'wpshadow' ),
				(int) $cc_check
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__(
					'Sensitive data may be stored in plaintext: %s. Plaintext storage violates PCI-DSS, GDPR, and creates severe security risk. Use WordPress encryption functions or proper credential management.',
					'wpshadow'
				),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/sensitive-data-in-database',
		);
	}
}
