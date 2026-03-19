<?php
/**
 * SSL Certificate Validity Not Checked Treatment
 *
 * Validates SSL certificate validity is monitored and checked.
 * Expired certificate = site shows security warning (users abandon site).
 * Broken trust chain = attackers impersonate (MITM attacks).
 *
 * **What This Check Does:**
 * - Checks if certificate expiration monitored
 * - Validates certificate chain is complete
 * - Tests if renewal scheduled/automated
 * - Confirms certificate matches domain
 * - Tests certificate validity date
 * - Returns severity if not monitored
 *
 * **Why This Matters:**
 * Expired certificate = broken trust. Scenarios:
 * - Admin forgets to renew SSL cert
 * - Certificate expires
 * - User visits site
 * - Browser: "Certificate expired. Your connection is not private."
 * - User leaves site (lost customer)
 *
 * **Business Impact:**
 * E-commerce site certificate expires on Friday. Admin sleeping.
 * Monday morning: customers can't access. Browser warning. All checkout
 * transactions blocked. Revenue loss: $100K+ (e-commerce weekend peak).
 * Customer emails: "Your site is hacked!". Reputation damaged. With monitoring:
 * admin gets email 30 days before expiration. Renews in 2 minutes. Revenue
 * protected.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Certificate always valid
 * - #9 Show Value: Prevents revenue loss from SSL issues
 * - #10 Beyond Pure: Continuous security monitoring
 *
 * **Related Checks:**
 * - Media SSL/HTTPS Enforcement (mixed content detection)
 * - HTTP to HTTPS Redirect (HTTPS enforcement)
 * - Certificate Chain Validation (trust verification)
 *
 * **Learn More:**
 * SSL certificate management: https://wpshadow.com/kb/ssl-cert-renewal
 * Video: SSL certificate monitoring (9min): https://wpshadow.com/training/ssl-monitoring
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Validity Not Checked Treatment Class
 *
 * Detects unchecked SSL certificate validity.
 *
 * **Detection Pattern:**
 * 1. Query site SSL certificate
 * 2. Check expiration date
 * 3. Test if within 30 days of expiration
 * 4. Validate certificate chain
 * 5. Confirm domain matches certificate
 * 6. Return severity if issues found
 *
 * **Real-World Scenario:**
 * Admin sets SSL certificate to auto-renew. Payment processor changes.
 * Auto-renewal fails (payment method invalid). Admin doesn't notice.
 * Certificate expires. Site shows warning. Users think site is hacked.
 * Revenue drops 60%. With monitoring: alert sent 30 days before. Admin
 * fixes payment method. Certificate renews automatically.
 *
 * **Implementation Notes:**
 * - Queries SSL certificate details
 * - Validates expiration and chain
 * - Tests domain matching
 * - Severity: critical (expired), high (expiring soon)
 * - Treatment: renew certificate or enable auto-renewal
 *
 * @since 1.6093.1200
 */
class Treatment_SSL_Certificate_Validity_Not_Checked extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-validity-not-checked';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Validity Not Checked';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate validity is monitored';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SSL_Certificate_Validity_Not_Checked' );
	}
}
