<?php
/**
 * Plugin Information Disclosure Treatment
 *
 * Detects plugins leaking sensitive information (version, paths, API keys).
 * Version disclosure = attacker knows exact vulnerabilities. Paths disclosure =
 * attacker knows where to find sensitive files. API key leakage = attacker can
 * use service as you (DoS, fraud).
 *
 * **What This Check Does:**
 * - Scans plugins for version disclosure in HTML/headers
 * - Detects if plugin version exposed (X-Plugin-Version header)
 * - Tests for file paths leaked in error messages
 * - Checks for API keys in client-side code
 * - Detects debug information exposure
 * - Tests for database query leakage
 *
 * **Why This Matters:**
 * Information disclosure = reconnaissance for attacks. Scenarios:
 * - Plugin version exposed in HTML comment: "Ver 2.1"
 * - Attacker checks CVE database for version 2.1
 * - Finds SQL injection vulnerability in version 2.1
 * - Attacker exploits SQL injection
 * - Database compromised
 *
 * **Business Impact:**
 * Plugin exposes version in HTTP headers. Attacker sees version 3.2.
 * Searches CVE database. Finds critical auth bypass (version 3.2 only).
 * Exploits bypass. Gains admin access. Site compromised. Recovery: $100K+.
 * If version hidden: attacker can't easily identify vulnerability.
 * Reconnaissance time goes from 30 minutes to days (gives you time to patch).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: System details hidden from attackers
 * - #9 Show Value: Slows reconnaissance phase
 * - #10 Beyond Pure: Security through concealment
 *
 * **Related Checks:**
 * - WordPress Version Disclosure (core version)
 * - Configuration Files Exposure (wp-config, secrets)
 * - Error Message Sanitization (query leakage)
 *
 * **Learn More:**
 * Information disclosure: https://wpshadow.com/kb/wordpress-information-disclosure
 * Video: Preventing info leaks (9min): https://wpshadow.com/training/info-disclosure
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.4031.1939
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Information_Disclosure Class
 *
 * Identifies plugins that leak sensitive information.
 *
 * **Detection Pattern:**
 * 1. Scan plugin output (HTML, headers, JS)
 * 2. Check for version strings (Ver: X.X.X)
 * 3. Detect file paths in error messages
 * 4. Look for API keys in client code
 * 5. Test debug info exposure (via inspect element)
 * 6. Return severity if sensitive info leaks
 *
 * **Real-World Scenario:**
 * Analytics plugin exposes version in JavaScript: window.pluginVersion="4.2.1".
 * Attacker inspects element. Sees version 4.2.1. Searches CVE. Finds remote
 * code execution (version 4.2.1 only). Exploits. Gets shell. With proper setup:
 * version hidden. Attacker can't easily identify vulnerability.
 *
 * **Implementation Notes:**
 * - Scans plugin output for version strings
 * - Tests for path/key exposure
 * - Checks headers for leaks
 * - Severity: medium (version exposed), high (API key exposed)
 * - Treatment: hide version, remove debug info, sanitize errors
 *
 * @since 1.4031.1939
 */
class Treatment_Plugin_Information_Disclosure extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-information-disclosure';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Information Disclosure';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins leaking sensitive system information';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Information_Disclosure' );
	}
}
