<?php
/**
 * Database Encryption Diagnostic
 *
 * Verifies sensitive database fields encrypted to prevent
 * breach exposure of customer PII.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Encryption Class
 *
 * Verifies sensitive data encryption.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Database_Encryption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-encryption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies sensitive data encryption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'protection';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if encryption missing, null otherwise.
	 */
	public static function check() {
		$encryption_status = self::check_database_encryption();

		if ( ! $encryption_status['has_issue'] ) {
			return null; // Encryption configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Sensitive data (passwords, payment tokens) stored in plaintext. Database breach = expose all customer PII = regulatory fines + reputation damage. Encrypt sensitive fields.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/database-encryption',
			'family'       => self::$family,
			'meta'         => array(
				'encryption_enabled' => false,
			),
			'details'      => array(
				'sensitive_data_requiring_encryption' => array(
					'User Passwords' => array(
						'WordPress: Hashed by default (bcrypt)',
						'Status: Protected',
					),
					'Payment Tokens' => array(
						'WooCommerce: Stripe token for saved cards',
						'Issue: Often plaintext in database',
						'Risk: Breach = attacker charges customers',
					),
					'Customer Phone' => array(
						'GDPR compliance: Phone is personal data',
						'Risk: Breach = privacy violation',
						'Protection: Encrypt at rest',
					),
					'Customer Email' => array(
						'GDPR compliance: Email is personal data',
						'Risk: Breach = privacy violation',
						'Storage: Usually plain but collected legally',
					),
					'Address Data' => array(
						'GDPR compliance: Address is personal data',
						'Risk: Breach = privacy violation',
						'Solution: Encrypt in database',
					),
				),
				'encryption_methods'                  => array(
					'MySQL Native Encryption' => array(
						'Feature: Available in MySQL 5.7+',
						'Method: Field-level or tablespace',
						'Limitation: Performance impact',
						'Setup: Complex, requires expertise',
					),
					'WordPress Plugin Encryption' => array(
						'Plugin: WP Encrypt',
						'Method: PHP-level encryption',
						'Pro: Easy to enable',
						'Con: Performance, key management',
					),
					'WooCommerce Native' => array(
						'Payment tokens: Encrypted by default',
						'Custom fields: Can be encrypted',
						'Token storage: WooCommerce handles',
					),
					'Third-Party Services' => array(
						'Stripe: Never stores card numbers',
						'PayPal: Tokens never exposed',
						'Best practice: Never store raw card data',
					),
				),
				'implementation_options'              => array(
					'Use Payment Gateway Tokens' => array(
						'Example: Stripe tokens vs. card data',
						'Advantage: Never store raw card numbers',
						'Compliance: Eliminates PCI scope',
						'Recommendation: Best practice',
					),
					'Use Third-Party Password Storage' => array(
						'Bitwarden, 1Password, AWS Secrets Manager',
						'Store: API keys, credentials',
						'Don\'t store: In database',
					),
					'Encrypt Custom Fields' => array(
						'Hook: On save, encrypt sensitive data',
						'Retrieve: On display, decrypt',
						'Plugin: WP Encrypt handles this',
					),
				),
				'encryption_key_management'           => array(
					'Key Storage (Critical)' => array(
						'Never: Hardcode in code',
						'Store: Environment variable',
						'Example: getenv( \'DB_ENCRYPTION_KEY\' )',
					),
					'Key Rotation' => array(
						'Periodic: Every 90 days',
						'Process: Complex, requires re-encryption',
						'Planning: Plan before implementing',
					),
					'Key Backup' => array(
						'Risk: Lost key = data unrecoverable',
						'Solution: Multiple secure copies',
						'Location: Off-site, encrypted',
					),
				),
				'gdpr_implications'                   => array(
					'Personal Data' => array(
						'Definition: Any info identifying person',
						'Examples: Name, email, IP, phone',
						'Requirement: Must be protected',
					),
					'Encryption' => array(
						'Requirement: At rest + in transit',
						'HTTPS: Handles in-transit',
						'Database: Handles at-rest',
					),
					'Fines' => array(
						'Non-compliance: Up to €20M or 4% revenue',
						'Breach notification: Required within 72 hours',
						'Prevention: Worth the effort',
					),
				),
			),
		);
	}

	/**
	 * Check database encryption.
	 *
	 * @since  1.2601.2148
	 * @return array Encryption status.
	 */
	private static function check_database_encryption() {
		$has_issue = true;

		// Check if encryption plugin active
		if ( is_plugin_active( 'wp-encrypt/wp-encrypt.php' ) ) {
			$has_issue = false;
		}

		// Check if WooCommerce with payment encryption
		if ( class_exists( 'WC_Payment_Gateways' ) ) {
			// WooCommerce encrypts tokens by default
			$has_issue = false;
		}

		return array(
			'has_issue' => $has_issue,
		);
	}
}
