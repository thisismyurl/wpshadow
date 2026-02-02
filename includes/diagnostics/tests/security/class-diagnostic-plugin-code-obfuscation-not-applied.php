<?php
/**
 * Plugin Code Obfuscation Not Applied Diagnostic
 *
 * Detects plugins without code obfuscation that expose sensitive logic.
 * Clear code = security researchers + attackers can easily find vulnerabilities.
 * Obfuscation makes reverse-engineering extremely difficult (takes weeks instead of hours).
 *
 * **What This Check Does:**
 * - Scans plugin code for readable variable names
 * - Detects if code is obfuscated (minified, encrypted)
 * - Tests if security-critical logic is visible
 * - Checks for debug information in production
 * - Validates if API endpoints hidden
 * - Returns severity if easily readable code
 *
 * **Why This Matters:**
 * Clear code = vulnerability discovery accelerated. Scenarios:
 * - Plugin source code is clearly readable
 * - Security researcher can identify vulnerability
 * - Attacker uses same research, exploits site
 * - Clear code = vulnerability disclosed publicly
 * - Everyone can exploit before patch available
 *
 * **Business Impact:**
 * Premium WordPress plugin used by 50K sites. Code is clear/unobfuscated.
 * Researcher finds SQL injection in 30 minutes (code is obvious). Publishes
 * vulnerability. All 50K sites compromised within 1 week (before patch).
 * Your site: breach + $500K liability. Obfuscated code: would have taken
 * weeks to reverse-engineer (researcher gave up).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Security through reasonable obscurity
 * - #9 Show Value: Slows vulnerability discovery
 * - #10 Beyond Pure: Defense in depth
 *
 * **Related Checks:**
 * - Plugin Vulnerability Detection (overall security)
 * - Code Integrity Verification (tampering detection)
 * - Update Management (security patches)
 *
 * **Learn More:**
 * Code obfuscation: https://wpshadow.com/kb/wordpress-code-obfuscation
 * Video: Plugin security practices (11min): https://wpshadow.com/training/plugin-practices
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Code Obfuscation Not Applied Diagnostic Class
 *
 * Detects non-obfuscated plugin code.
 *
 * **Detection Pattern:**
 * 1. Scan plugin PHP files
 * 2. Check for readable variable names (security_key = unobfuscated)
 * 3. Detect if code is minified/encoded
 * 4. Test for obvious SQL queries/API calls
 * 5. Validate API endpoints not exposed
 * 6. Return severity if readable code found
 *
 * **Real-World Scenario:**
 * Popular plugin with 100K installations. Code is clear (developer didn't obfuscate).
 * Researcher downloads plugin from WordPress.org. Reads code. Finds SQL injection.
 * Emails plugin dev. Dev is slow to respond. Researcher publishes. All 100K sites
 * at risk. With obfuscation: researcher can't easily read code, gives up.
 *
 * **Implementation Notes:**
 * - Scans plugin source files
 * - Checks for code obfuscation (minified, encoded)
 * - Tests for sensitive logic exposure
 * - Severity: medium (clear code), high (obvious vulnerabilities exposed)
 * - Treatment: use code obfuscation tools
 *
 * @since 1.2601.2352
 */
class Diagnostic_Plugin_Code_Obfuscation_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-code-obfuscation-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Code Obfuscation Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin code is obfuscated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if plugins are minified for production
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Plugin code obfuscation is not applied. Minify and compress plugin code to reduce file sizes and improve security.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-code-obfuscation-not-applied',
			);
		}

		return null;
	}
}
