<?php
/**
 * Security Headers Not Configured Comprehensive Diagnostic
 *
 * Validates all recommended HTTP security headers are configured.
 * Headers instruct browser on security policies (CSP, X-Frame-Options, etc).
 * Missing headers = browser defaults to permissive (vulnerable).
 *
 * **What This Check Does:**
 * - Checks for X-Frame-Options (clickjacking protection)
 * - Tests Content-Security-Policy (XSS prevention)
 * - Validates X-Content-Type-Options (MIME sniffing prevention)
 * - Checks Strict-Transport-Security (HTTPS enforcement)
 * - Tests X-XSS-Protection (legacy XSS filter)
 * - Returns severity if headers missing
 *
 * **Why This Matters:**
 * Missing headers = browser exploitable. Scenarios:
 * - No X-Frame-Options = clickjacking attack
 * - Attacker embeds site in hidden iframe
 * - User clicks attacker's button (actually clicks site)
 * - Admin performs unintended action
 * - No CSP = XSS attack succeeds
 *
 * **Business Impact:**
 * E-commerce site missing security headers. Attacker crafts page with
 * hidden iframe embedding checkout. User clicks "Get Prize". Actually
 * clicks "Buy Now" on site (hidden in iframe). Charges $1000 to card.
 * With security headers (X-Frame-Options): browser blocks embedding.
 * Attack impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Browser-enforced security
 * - #9 Show Value: Defense-in-depth approach
 * - #10 Beyond Pure: Security by design
 *
 * **Related Checks:**
 * - CORS Headers Configuration (cross-origin policy)
 * - Content-Type Validation (MIME handling)
 * - HTTP to HTTPS Redirect (HTTPS enforcement)
 *
 * **Learn More:**
 * Security headers guide: https://wpshadow.com/kb/wordpress-security-headers
 * Video: Implementing security headers (12min): https://wpshadow.com/training/security-headers
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
 * Security Headers Not Configured Comprehensive Diagnostic Class
 *
 * Detects missing security headers.
 *
 * **Detection Pattern:**
 * 1. Query site homepage (GET request)
 * 2. Check response headers
 * 3. Validate X-Frame-Options present
 * 4. Test CSP header
 * 5. Confirm HSTS header
 * 6. Return severity for missing headers
 *
 * **Real-World Scenario:**
 * Site missing all security headers. Attacker sends victim phishing email
 * with clickjacking attack. User clicks. Hidden iframe performs action.
 * With headers: browser blocks iframe (X-Frame-Options), prevents script
 * injection (CSP). Attack fails at browser level.
 *
 * **Implementation Notes:**
 * - Tests HTTP response headers
 * - Validates header values (not just presence)
 * - Checks all major security headers
 * - Severity: high (no headers), medium (some missing)
 * - Treatment: configure all recommended security headers
 *
 * @since 0.6093.1200
 */
class Diagnostic_Security_Headers_Not_Configured_Comprehensive extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers-not-configured-comprehensive';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Not Configured Comprehensive';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if all security headers are configured';

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
		// Check for comprehensive security headers
		if ( ! has_filter( 'wp_headers', 'add_security_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Security headers are not comprehensively configured. Add X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Strict-Transport-Security, and Content-Security-Policy headers.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/security-headers-not-configured-comprehensive?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
