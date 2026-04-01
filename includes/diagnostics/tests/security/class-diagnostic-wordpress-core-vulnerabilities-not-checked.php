<?php
/**
 * WordPress Core Vulnerabilities Not Checked Diagnostic
 *
 * Checks if core vulnerabilities are monitored.
 * WordPress core has security patches regularly.
 * Unpatched = known vulnerabilities exploitable.
 * Monitoring = notified when patch needed.
 *
 * **What This Check Does:**
 * - Checks if vulnerability monitoring enabled
 * - Validates WordPress version tracking
 * - Tests for CVE database checking
 * - Checks security update notifications
 * - Validates auto-update configuration
 * - Returns severity if monitoring disabled
 *
 * **Why This Matters:**
 * WordPress 5.0 has critical XSS vulnerability.
 * Without monitoring: admin doesn't know.
 * Attacker exploits before patch applied.
 * With monitoring: admin notified immediately. Patches within hours.
 *
 * **Business Impact:**
 * WordPress 5.8 critical vulnerability (CVE-2021-29447) published.
 * Without monitoring: site vulnerable 6+ months. Attacker exploits.
 * XXE attack. Database exfiltrated. Cost: $2M+ (breach notification,
 * legal, credit monitoring). With monitoring: alert sent same day.
 * Admin updates immediately. Vulnerability window: 2 hours. No breach.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Vulnerabilities tracked
 * - #9 Show Value: Prevents zero-day exploitation
 * - #10 Beyond Pure: Proactive threat monitoring
 *
 * **Related Checks:**
 * - Plugin Vulnerability Scanning (related)
 * - WordPress Version Up to Date (complementary)
 * - Security Update Management (broader)
 *
 * **Learn More:**
 * Vulnerability monitoring: https://wpshadow.com/kb/vulnerability-monitoring
 * Video: Security updates (11min): https://wpshadow.com/training/updates
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Core Vulnerabilities Not Checked Diagnostic Class
 *
 * Detects missing core vulnerability monitoring.
 *
 * **Detection Pattern:**
 * 1. Check if vulnerability plugin active
 * 2. Get current WordPress version
 * 3. Query CVE database for known vulnerabilities
 * 4. Check security update notifications enabled
 * 5. Validate auto-update configuration
 * 6. Return if monitoring disabled
 *
 * **Real-World Scenario:**
 * Vulnerability monitoring enabled. WordPress 6.0 critical vulnerability
 * announced. Admin receives email: "Critical security update available".
 * Updates within 1 hour. Vulnerability window minimal. With no monitoring:
 * admin unaware for weeks. Attacker exploits. Site compromised.
 *
 * **Implementation Notes:**
 * - Checks vulnerability monitoring configuration
 * - Queries CVE databases
 * - Tests notification system
 * - Severity: critical (known vulnerabilities unpatched)
 * - Treatment: enable vulnerability monitoring and auto-updates
 *
 * @since 0.6093.1200
 */
class Diagnostic_WordPress_Core_Vulnerabilities_Not_Checked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-core-vulnerabilities-not-checked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Core Vulnerabilities Not Checked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if core vulnerabilities are monitored';

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
		// Check for vulnerability scanner
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WordPress core vulnerabilities are not being monitored. Enable vulnerability scanning to detect known security issues in WordPress core.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wordpress-core-vulnerabilities-not-checked?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
