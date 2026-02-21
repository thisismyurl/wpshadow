<?php
/**
 * XSS Protection Treatment
 *
 * Issue #4884: User Input Not Escaped on Output (XSS Vulnerability)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if output is properly escaped to prevent XSS attacks.
 * Cross-Site Scripting lets attackers inject malicious JavaScript.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_XSS_Protection Class
 *
 * Checks for:
 * - All output escaped with esc_html(), esc_attr(), esc_url()
 * - No direct echo of user input
 * - JavaScript strings escaped with esc_js()
 * - HTML allowed only via wp_kses() with strict rules
 * - No innerHTML assignment without sanitization
 * - Content Security Policy headers
 * - X-XSS-Protection header enabled
 *
 * Why this matters:
 * - XSS is #3 OWASP vulnerability (A03:2021)
 * - Attackers steal sessions, cookies, credentials
 * - Attackers deface sites
 * - Attackers redirect to phishing sites
 * - One XSS hole compromises all users
 *
 * @since 1.6050.0000
 */
class Treatment_XSS_Protection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'xss-protection';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'User Input Not Escaped on Output (XSS Vulnerability)';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if output is escaped to prevent Cross-Site Scripting (XSS) attacks';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_XSS_Protection' );
	}
}
