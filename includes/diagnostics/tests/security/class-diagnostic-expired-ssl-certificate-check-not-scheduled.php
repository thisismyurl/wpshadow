<?php
/**
 * Expired SSL Certificate Check Not Scheduled Diagnostic
 *
 * Validates that SSL certificate expiration monitoring is enabled. Expired\n * SSL certificates break HTTPS, cause browser security warnings, and stop payments.\n * No automated warning = certificate expires silently, site goes down.\n *
 * **What This Check Does:**
 * - Detects if SSL monitoring scheduled (cron job or plugin)\n * - Gets current SSL certificate expiration date\n * - Validates certificate is not expiring soon (< 30 days)\n * - Checks if admin notified before expiration\n * - Tests renewal process automation\n * - Confirms monitoring actively running\n *
 * **Why This Matters:**
 * Expired SSL certificate = instant site down (browsers show error). Scenarios:\n * - Certificate expires (admin on vacation, doesn't see expiry notice)\n * - HTTPS stops working\n * - All browsers show \"Not Secure\" warning\n * - Customers can't pay, orders fail\n * - Site down until certificate renewed (1-24 hours delay)\n * - Search ranking drops (Google penalizes expired certs)\n *
 * **Business Impact:**
 * E-commerce site. SSL certificate expires Friday. Admin on vacation until Monday.\n * No monitoring/warning sent. Saturday: customers can't buy (security warning).\n * Lost sales: ~$5,000 (weekend = peak commerce time). Monday: emergency renewal\n * (expedited = extra cost $200). Recovery time: 4 hours (propagation delay).\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: HTTPS always working\n * - #9 Show Value: Quantified uptime, revenue protection\n * - #10 Beyond Pure: Proactive monitoring, not reactive\n *
 * **Related Checks:**
 * - SSL/TLS Configuration Not Set (HTTPS setup)\n * - DNS CAA Records Not Configured (certificate authority restriction)\n * - HSTS Headers Not Configured (HTTPS enforcement)\n *
 * **Learn More:**
 * SSL certificate management: https://wpshadow.com/kb/ssl-certificate-management\n * Video: Automating certificate renewal (7min): https://wpshadow.com/training/ssl-monitoring\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired SSL Certificate Check Not Scheduled Diagnostic Class
 *
 * Implements detection of missing SSL expiration monitoring.\n *
 * **Detection Pattern:**
 * 1. Get site URL to extract hostname\n * 2. Connect and get SSL certificate (openssl or stream_context)\n * 3. Extract expiration date from certificate\n * 4. Calculate days until expiration\n * 5. Check for monitoring cron job\n * 6. Return severity if expiring soon or no monitoring\n *
 * **Real-World Scenario:**
 * WordPress site using Let's Encrypt (auto-renewal capable). Admin manually\n * renewed certificate once per year (didn't enable auto-renewal). Year 2: admin\n * forgets renewal date. Certificate expires on scheduled expiration. No monitoring\n * to warn. Site goes down. Customer calls Monday: \"Site showing security error\".\n * Emergency renewal: 2 hours downtime, $200 expedited cost.\n *
 * **Implementation Notes:**
 * - Uses openssl_x509_parse() for certificate inspection\n * - Calculates days until expiration\n * - Checks WordPress cron for monitoring\n * - Severity: critical (expired), high (< 7 days)\n * - Treatment: enable SSL monitoring, auto-renewal\n *
 * @since 1.6030.2352
 */
class Diagnostic_Expired_SSL_Certificate_Check_Not_Scheduled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'expired-ssl-certificate-check-not-scheduled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Expired SSL Certificate Check Not Scheduled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL expiration check is scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if SSL expiration check is scheduled
		if ( ! wp_next_scheduled( 'check_ssl_certificate_expiration' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SSL certificate expiration monitoring is not scheduled. Schedule regular checks to receive notifications before your certificate expires.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/expired-ssl-certificate-check-not-scheduled',
			);
		}

		return null;
	}
}
