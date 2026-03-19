<?php
/**
 * XSS Content Security Policy Not Configured Treatment
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
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XSS Content Security Policy Not Configured Treatment Class
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
class Treatment_XSS_Content_Security_Policy_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-content-security-policy-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Content Security Policy Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSP is configured';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XSS_Content_Security_Policy_Not_Configured' );
	}
}
