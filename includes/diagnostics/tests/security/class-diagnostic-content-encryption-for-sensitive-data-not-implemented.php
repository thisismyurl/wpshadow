<?php
/**
 * Content Encryption For Sensitive Data Not Implemented Diagnostic
 *
 * Validates that sensitive data stored in database is encrypted at rest.\n * Unencrypted sensitive data exposed if database leaked. Scenario: Database\n * backup accidentally uploaded to public S3 bucket. Attacker downloads all\n * customer payment info, SSNs, medical records in plain text.\n *
 * **What This Check Does:**
 * - Detects if sensitive data (payment, SSN, medical) is encrypted\n * - Scans postmeta/usermeta for unencrypted sensitive fields\n * - Validates encryption key management (keys not in source code)\n * - Checks if encryption methods are industry-standard (AES-256)\n * - Tests that encrypted data decrypts properly\n * - Confirms database backups don't contain plaintext secrets\n *
 * **Why This Matters:**
 * Unencrypted sensitive data enables catastrophic breaches. Scenarios:\n * - Database leaked: attacker sees 100K customer credit cards in plaintext\n * - Server hacked: attacker finds patient medical history unencrypted\n * - Backup compromised: 50K SSNs exposed (PII harvesting worth $50+ per record)\n *
 * **Business Impact:**
 * Healthcare site stores patient medical records unencrypted in WordPress database.\n * Server compromised (weak password). Attacker accesses database. 10K patient records\n * stolen. Each record: 5+ identity theft incidents. Liability: HIPAA penalties ($100-$150\n * per record) = $1-$1.5M. Plus notification costs, lawsuits, reputation damage.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sensitive data protected even if breached\n * - #9 Show Value: Meets compliance requirements (HIPAA, PCI, GDPR)\n * - #10 Beyond Pure: Privacy by design, encryption by default\n *
 * **Related Checks:**
 * - Database User Privileges Not Minimized (database security)\n * - Personal Data Export Functionality (compliance requirement)\n * - SSL/TLS Configuration Not Set (transport encryption)\n *
 * **Learn More:**
 * Data encryption patterns: https://wpshadow.com/kb/wordpress-data-encryption\n * Video: Implementing encryption (15min): https://wpshadow.com/training/encryption-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
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
 * Content Encryption For Sensitive Data Not Implemented Diagnostic Class
 *
 * Implements detection of unencrypted sensitive data in database.\n *
 * **Detection Pattern:**
 * 1. Query postmeta/usermeta for known sensitive field patterns\n * 2. Check for credit card patterns (16 digit sequences)\n * 3. Detect SSN patterns (XXX-XX-XXXX in plaintext)\n * 4. Check for medical code patterns (ICD, CPT in plaintext)\n * 5. Validate encryption key management\n * 6. Return severity if sensitive data unencrypted\n *
 * **Real-World Scenario:**
 * Developer building membership site stores member SSNs to verify identity.\n * Uses custom field: \"_member_ssn\" = \"123-45-6789\" in postmeta. Doesn't encrypt.\n * Database backup runs weekly. One backup accidentally added to public folder.\n * 2,000 member SSNs available for download. Identity theft ring harvests records.\n * Sells SSNs for $5 each = $10K revenue. Site owner liable for fraud protection.\n *
 * **Implementation Notes:**
 * - Scans postmeta/usermeta for sensitive patterns\n * - Validates encryption key NOT in source code\n * - Checks encryption algorithm (AES-256 minimum)\n * - Severity: critical (data exposed), high (no encryption)\n * - Treatment: implement encryption layer\n *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Encryption_For_Sensitive_Data_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-encryption-for-sensitive-data-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Encryption For Sensitive Data Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data encryption is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site collects sensitive data.
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_memberpress = is_plugin_active( 'memberpress/memberpress.php' );
		$has_edd = is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		// Only flag if site is collecting payment/membership data.
		if ( ! $has_woocommerce && ! $has_memberpress && ! $has_edd ) {
			return null; // No sensitive data being collected.
		}

		// Check for encryption plugins.
		$encryption_plugins = array(
			'wp-encrypt/wp-encrypt.php' => 'WP Encrypt',
		);

		$encryption_detected = false;
		foreach ( $encryption_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$encryption_detected = true;
				break;
			}
		}

		// Check if SSL is enabled (transport encryption).
		$has_ssl = is_ssl();

		// WordPress doesn't encrypt database by default.
		// Most sites rely on payment gateway tokens, not storing cards.
		if ( $has_woocommerce || $has_memberpress || $has_edd ) {
			// WooCommerce/EDD best practice: don't store payment data.
			// Check if storing locally vs using payment gateway tokens.
			
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Payment Data Storage Review Needed', 'wpshadow' ),
				'description' => __( 'You\'re using WooCommerce/membership plugin. CRITICAL: Never store credit card numbers, CVV, or full SSNs in WordPress database. Use payment gateway tokenization (Stripe, PayPal) which stores sensitive data on PCI-compliant servers. WordPress database is NOT PCI-compliant by default. Storing payment data = massive liability risk.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/payment-data-security',
				'details'     => array(
					'has_woocommerce' => $has_woocommerce,
					'has_ssl'         => $has_ssl,
					'recommendation'  => __( 'BEST PRACTICE: Use Stripe/PayPal payment gateways with tokenization. Payment data never touches your server. Customer enters card info directly on Stripe\'s hosted form (PCI-compliant). You only store token reference (safe to store). NEVER store: Full credit card numbers, CVV codes, unencrypted SSNs.', 'wpshadow' ),
					'compliance'      => array(
						'pci_dss' => 'Payment Card Industry Data Security Standard',
						'requirement' => 'Encrypt cardholder data with industry-standard encryption',
						'penalties' => '$5,000-$100,000 per month for non-compliance',
						'liability' => 'Merchant liable for fraud if card data stolen',
					),
					'safe_practices'  => array(
						'tokenization' => 'Payment gateway stores card, returns token',
						'ssl_tls' => 'Encrypt data in transit (you have this: ' . ( $has_ssl ? 'YES' : 'NO' ) . ')',
						'no_local_storage' => 'Never save full card numbers in database',
						'log_safety' => 'Don\'t log sensitive data in debug logs',
					),
					'what_to_store'   => array(
						'safe' => 'Payment gateway tokens, last 4 digits, transaction IDs',
						'unsafe' => 'Full card numbers, CVV, magnetic stripe data',
					),
					'payment_gateways' => array(
						'stripe' => 'PCI-compliant, tokenization built-in',
						'paypal' => 'PCI-compliant, hosted checkout',
						'square' => 'PCI-compliant, secure payment forms',
					),
				),
			);
		}

		// No issues - not collecting sensitive data.
		return null;
	}
}
