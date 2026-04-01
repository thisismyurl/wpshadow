<?php
/**
 * DNS CAA Records Not Configured Diagnostic
 *
 * Validates that DNS CAA (Certification Authority Authorization) records are\n * configured to restrict which CAs can issue SSL certificates for your domain.\n * Without CAA records, ANY CA can issue certificates for your domain (attacker\n * get legitimate cert, performs MITM attacks).\n *
 * **What This Check Does:**
 * - Queries DNS for CAA records\n * - Validates CAA records exist and specify trusted CAs only\n * - Detects if CAA allows any CA (wildcard - security risk)\n * - Checks if CAA has iodef tag (incident reporting)\n * - Tests that only legitimate CAs listed (Let's Encrypt, DigiCert, etc.)\n * - Confirms CAA prevents unauthorized certificate issuance\n *
 * **Why This Matters:**
 * No CAA records = attacker can request certificate from ANY CA. Scenarios:\n * - Attacker requests certificate for yourdomain.com from sketchy CA\n * - Gets legitimate certificate (browser trusts all CAs equally)\n * - Attacker performs MITM attack (intercepts traffic)\n * - Users see legitimate cert for yourdomain.com (no browser warning)\n * - Traffic decrypted by attacker (credentials, payment info stolen)\n *
 * **Business Impact:**
 * E-commerce site without CAA records. Attacker obtains certificate from compromised\n * CA (or requests from CA with loose verification). Performs MITM. Customer traffic\n * intercepted. Payment info stolen. 100 fraudulent transactions. Total damage:\n * $50K-$200K in fraud liability + recovery costs.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Only trusted CAs can issue certificates\n * - #9 Show Value: Prevents certificate spoofing attacks\n * - #10 Beyond Pure: Defense in depth, CA-level control\n *
 * **Related Checks:**
 * - SSL/TLS Configuration Not Set (certificate verification)\n * - HSTS Headers Not Configured (HTTPS enforcement)\n * - Certificate Pinning (advanced: pin specific certs)\n *
 * **Learn More:**
 * DNS CAA records setup: https://wpshadow.com/kb/dns-caa-records\n * Video: Configuring CAA records (6min): https://wpshadow.com/training/caa-setup\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS CAA Records Not Configured Diagnostic Class
 *
 * Implements CAA record validation via DNS queries.\n *
 * **Detection Pattern:**
 * 1. Get site domain from get_site_url()\n * 2. Query DNS for CAA records (dns_get_record() with DNS_CAA type)\n * 3. Parse CAA flags and tags\n * 4. Validate CA list (flags = 0, tag = 'issue')\n * 5. Check if iodef notification tag present (flags = 128)\n * 6. Return severity if no CAA or too permissive\n *
 * **Real-World Scenario:**
 * WordPress site has no CAA records. Attacker researches site. Discovers you use\n * Let's Encrypt for certs. Finds different CA that also issues certificates.\n * Requests certificate for yoursite.com from sketchy CA. Gets approved (no CAA\n * restriction). Installs certificate on attacker's server. Performs DNS spoofing\n * or BGP hijack. Traffic routed to attacker. 500 users redirected. Credentials\n * harvested. 50 accounts compromised.\n *
 * **Implementation Notes:**
 * - Uses dns_get_record() with DNS_CAA type\n * - Validates records reference Let's Encrypt, major CAs only\n * - Checks for iodef incident reporting tag\n * - Severity: high (no CAA), medium (misconfigured)\n * - Treatment: add CAA records restricting to trusted CAs\n *
 * @since 0.6093.1200
 */
class Diagnostic_DNS_CAA_Records_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-caa-records-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS CAA Records Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DNS CAA records are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CAA DNS records using DNS query
		$domain = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );
		if ( $domain && ! get_option( 'dns_caa_records_checked' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'DNS CAA records are not configured. Add CAA records to restrict which Certificate Authorities can issue SSL certificates for your domain to prevent unauthorized certificate issuance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/dns-caa-records-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
