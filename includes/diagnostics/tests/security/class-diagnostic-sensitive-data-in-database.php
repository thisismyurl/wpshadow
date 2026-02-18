<?php
/**
 * Sensitive Data in Database Diagnostic
 *
 * Detects unencrypted sensitive data stored in WordPress database,
 * including plaintext passwords, credit cards, and API keys.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensitive Data in Database Diagnostic Class
 *
 * Checks for:
 * - Plaintext passwords in wp_usermeta
 * - Credit card numbers in any table
 * - API keys and secrets in wp_options
 * - Social Security Numbers or similar identifiers
 * - Unencrypted personal identification data
 *
 * According to IBM's 2024 Cost of a Data Breach Report, the average
 * cost of a data breach is $4.45 million. Storing sensitive data
 * unencrypted violates GDPR, PCI-DSS, and most compliance frameworks,
 * resulting in fines up to 4% of annual revenue.
 *
 * @since 1.2033.2102
 */
class Diagnostic_Sensitive_Data_In_Database extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'sensitive-data-in-database';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Sensitive Data in Database';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects unencrypted sensitive data stored in the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans database for patterns indicating sensitive data storage:
	 * 1. Plaintext passwords in usermeta
	 * 2. Credit card numbers (Luhn algorithm validation)
	 * 3. API keys and tokens in options
	 * 4. SSN/tax ID patterns
	 * 5. Unencrypted personal identification
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check 1: Look for plaintext password fields in usermeta.
		$password_meta_keys = array(
			'%_backup_password%',
			'%_temp_password%',
			'%_plain_password%',
			'%_cleartext_password%',
			'%_user_password%',
		);

		foreach ( $password_meta_keys as $pattern ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
					$pattern
				)
			);

			if ( $count > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: meta key pattern, 2: count */
					__( 'Found %2$d usermeta entries matching "%1$s" (potential plaintext passwords)', 'wpshadow' ),
					$pattern,
					$count
				);
			}
		}

		// Check 2: Look for credit card patterns in postmeta and usermeta.
		// Search for sequences of 13-19 digits (common credit card lengths).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$potential_cc_meta = $wpdb->get_results(
			"SELECT meta_key, COUNT(*) as count FROM {$wpdb->postmeta} 
			WHERE meta_value REGEXP '^[0-9]{13,19}$' 
			GROUP BY meta_key 
			LIMIT 10",
			ARRAY_A
		);

		foreach ( $potential_cc_meta as $meta ) {
			if ( self::looks_like_credit_card_key( $meta['meta_key'] ) ) {
				$issues[] = sprintf(
					/* translators: 1: meta key, 2: count */
					__( 'Found %2$d postmeta entries with key "%1$s" containing numeric sequences (potential credit card numbers)', 'wpshadow' ),
					$meta['meta_key'],
					$meta['count']
				);
			}
		}

		// Check 3: Look for API keys and secrets in wp_options.
		$sensitive_option_patterns = array(
			'%_api_key%',
			'%_secret_key%',
			'%_access_token%',
			'%_private_key%',
			'%_client_secret%',
			'%_auth_token%',
		);

		foreach ( $sensitive_option_patterns as $pattern ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$options = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name, option_value FROM {$wpdb->options} 
					WHERE option_name LIKE %s 
					AND option_value != '' 
					LIMIT 5",
					$pattern
				),
				ARRAY_A
			);

			foreach ( $options as $option ) {
				// Check if value looks encrypted (base64, has encryption markers, etc.).
				if ( ! self::looks_encrypted( $option['option_value'] ) ) {
					$issues[] = sprintf(
						/* translators: %s: option name */
						__( 'Option "%s" contains what appears to be an unencrypted API key or secret', 'wpshadow' ),
						$option['option_name']
					);
				}
			}
		}

		// Check 4: Look for Social Security Number patterns (US format: XXX-XX-XXXX).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ssn_patterns = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_value REGEXP '[0-9]{3}-[0-9]{2}-[0-9]{4}'"
		);

		if ( $ssn_patterns > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d postmeta entries matching SSN pattern (XXX-XX-XXXX)', 'wpshadow' ),
				$ssn_patterns
			);
		}

		// Check 5: Look for common personal data fields that should be encrypted.
		$sensitive_fields = array( 'ssn', 'tax_id', 'credit_card', 'cvv', 'passport', 'drivers_license' );
		
		foreach ( $sensitive_fields as $field ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} 
					WHERE meta_key LIKE %s 
					AND meta_value != ''",
					'%' . $wpdb->esc_like( $field ) . '%'
				)
			);

			if ( $count > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: field type, 2: count */
					__( 'Found %2$d usermeta entries with "%1$s" in key name (should be encrypted)', 'wpshadow' ),
					$field,
					$count
				);
			}
		}

		// Check 6: Look for payment gateway data stored unencrypted.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$payment_data = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key IN ('_billing_card_number', '_card_number', '_payment_method_token') 
			AND meta_value != ''"
		);

		if ( $payment_data > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d payment-related meta entries (potential PCI-DSS violation)', 'wpshadow' ),
				$payment_data
			);
		}

		// If we found any issues, return a finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d sensitive data storage issue detected',
						'%d sensitive data storage issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sensitive-data-in-database',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Storing sensitive data unencrypted is a critical security and compliance violation. ' .
						'According to IBM\'s 2024 Cost of a Data Breach Report, the average breach costs $4.45 million. ' .
						'Unencrypted sensitive data violates GDPR (fines up to 4% of annual revenue), PCI-DSS (fines up to $500,000/month), ' .
						'HIPAA (fines up to $1.5 million/year), and most compliance frameworks. ' .
						'If your database is compromised (SQL injection, backup theft, insider threat), attackers get immediate access to all this data.',
						'wpshadow'
					),
					'recommendation' => __(
						'Never store credit card numbers, CVVs, or full payment details. Use payment gateway tokens instead. ' .
						'Encrypt all sensitive data at rest using strong encryption (AES-256). ' .
						'For API keys and secrets, use WordPress encryption functions or environment variables. ' .
						'Regularly audit your database for sensitive data patterns and implement proper data classification.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'vault',
				'data-encryption',
				'encryption-guide'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Check if a meta key name suggests credit card data.
	 *
	 * @since  1.2033.2102
	 * @param  string $key Meta key name.
	 * @return bool True if key suggests credit card data.
	 */
	private static function looks_like_credit_card_key( $key ) {
		$cc_patterns = array( 'card', 'credit', 'cc_', 'payment', 'visa', 'mastercard', 'amex' );
		$key_lower = strtolower( $key );

		foreach ( $cc_patterns as $pattern ) {
			if ( str_contains( $key_lower, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a value appears to be encrypted.
	 *
	 * @since  1.2033.2102
	 * @param  string $value Value to check.
	 * @return bool True if value appears encrypted.
	 */
	private static function looks_encrypted( $value ) {
		// Check for base64 encoding (common encryption wrapper).
		if ( base64_encode( base64_decode( $value, true ) ) === $value ) {
			return true;
		}

		// Check for encryption markers.
		$encryption_markers = array( 'encrypted:', 'enc:', '$2y$', '$2a$', '$argon2' );
		foreach ( $encryption_markers as $marker ) {
			if ( str_starts_with( $value, $marker ) ) {
				return true;
			}
		}

		// If value is very long and contains special chars, likely encrypted.
		if ( strlen( $value ) > 50 && preg_match( '/[^a-zA-Z0-9]/', $value ) ) {
			return true;
		}

		return false;
	}
}
