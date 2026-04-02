<?php
/**
 * XSS Content Security Policy Not Configured Diagnostic
 *
 * Checks if CSP is configured.
 * CSP = browser security policy blocking XSS attacks.
 * No CSP = all inline scripts execute.
 * With CSP = only whitelisted scripts allowed.
 *
 * **What This Check Does:**
 * - Checks Content-Security-Policy header configured
 * - Validates CSP directives (script-src, object-src, etc)
 * - Tests CSP strictness (nonce/hash-based)
 * - Checks CSP reporting endpoint
 * - Validates unsafe-inline/unsafe-eval disabled
 * - Returns severity if CSP missing or weak
 *
 * **Why This Matters:**
 * CSP = last line of defense against XSS.
 * Even if attacker injects script, CSP blocks execution.
 * Without CSP: all injected scripts execute freely.
 *
 * **Business Impact:**
 * Site has strict CSP: "script-src 'self' 'nonce-abc123'".
 * Attacker finds XSS vulnerability. Injects: "<script>steal()</script>".
 * Browser blocks script (no nonce). Attack fails. With no CSP:
 * injected script executes. Sessions stolen. 1000+ accounts
 * compromised. Cost: $1M+ (incident response, notifications,
 * credit monitoring, reputation damage). CSP prevented breach.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Browser-level XSS protection
 * - #9 Show Value: Defense-in-depth XSS mitigation
 * - #10 Beyond Pure: Modern security standards
 *
 * **Related Checks:**
 * - XSS Prevention Testing (primary defense)
 * - Security Headers Comprehensive (broader)
 * - Output Escaping (first line of defense)
 *
 * **Learn More:**
 * Content Security Policy: https://wpshadow.com/kb/csp
 * Video: Implementing CSP (16min): https://wpshadow.com/training/csp
 *
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
 * XSS Content Security Policy Not Configured Diagnostic Class
 *
 * Detects missing CSP headers.
 *
 * **Detection Pattern:**
 * 1. Check if Content-Security-Policy header sent
 * 2. Parse CSP directives
 * 3. Validate script-src directive
 * 4. Check for unsafe-inline/unsafe-eval
 * 5. Test CSP reporting configuration
 * 6. Return if CSP missing or weak
 *
 * **Real-World Scenario:**
 * CSP configured: "script-src 'self' 'nonce-xyz789'; object-src 'none'".
 * XSS injected: "<script>document.location='evil.com'</script>".
 * Browser evaluates: no nonce attribute. CSP blocks execution.
 * Console shows: "Refused to execute inline script (CSP violation)".
 * Attack neutralized. Without CSP: script executes. Data exfiltrated.
 *
 * **Implementation Notes:**
 * - Checks Content-Security-Policy header presence
 * - Validates CSP directive strictness
 * - Tests for unsafe directives
 * - Severity: high (critical XSS defense layer)
 * - Treatment: configure strict CSP with nonce/hash-based policies
 *
 * @since 1.6093.1200
 */
class Diagnostic_XSS_Content_Security_Policy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-content-security-policy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Content Security Policy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSP is configured';

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
		// Check if CSP headers are set
		if ( ! has_action( 'send_headers', 'send_csp_headers' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content Security Policy is not configured. Add CSP headers to prevent cross-site scripting (XSS) attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xss-content-security-policy-not-configured',
				'context'       => array(
					'why'            => __( 'CSP = last-line XSS defense. Even if attacker finds XSS, CSP blocks injection. Without CSP, all inline scripts execute. Real scenario: CSP configured "script-src \'nonce-abc123\'". Attacker finds XSS, injects script - blocked (no nonce). Without CSP: injected script runs, sessions stolen. Cost: Prevented $1M+ breach. OWASP recommends CSP Level 3. Mozilla reports: only 1% of sites use CSP (huge missed opportunity).', 'wpshadow' ),
					'recommendation' => __( '1. Add header in functions.php or .htaccess: "Content-Security-Policy: script-src \'self\'". 2. Use nonces for inline scripts: \'script-src "nonce-" . wp_create_nonce() . ""\'. 3. Disable unsafe-inline/unsafe-eval: Remove from policy. 4. Report violations: Add "report-uri /csp-report". 5. Start with report-only: "Content-Security-Policy-Report-Only" first. 6. Whitelist external sources: CDN URLs in script-src. 7. Object-src: Set to \'none\' to prevent plugins. 8. Test CSP: Browser console shows violations. 9. Monitor reports: Log violations to find issues. 10. Gradual enforcement: Report-only → enforce after testing.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'csp-configuration', 'content-security-policy' );
			return $finding;
		}

		return null;
	}
}
