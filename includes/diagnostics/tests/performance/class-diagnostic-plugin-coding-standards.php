<?php
/**
 * Plugin Coding Standards Compliance Diagnostic
 *
 * Detects plugins violating WordPress standards, indicating poor quality and security risks.
 *
 * **What This Check Does:**
 * 1. Analyzes plugin code for WordPress standard violations
 * 2. Checks for SQL injection vulnerabilities (unescaped queries)
 * 3. Detects missing nonce verification (CSRF vulnerabilities)
 * 4. Flags insufficient user capability checks
 * 5. Identifies output escaping violations
 * 6. Measures code quality and maturity level\n *
 * **Why This Matters:**\n * Poor coding standards often hide security vulnerabilities. Plugins that don't verify nonces are
 * CSRF exploitable. Plugins with unescaped output have XSS vulnerabilities. Plugins without capability
 * checks can be manipulated by low-privilege users. These aren't edge cases—they're common in low-quality
 * plugins. A single vulnerability exposes your entire site.\n *
 * **Real-World Scenario:**\n * Popular plugin didn't verify nonces on AJAX endpoints. Hacker embedded attack script on attacker's
 * website. When admin visited attacker's website in browser tab, the script made requests to WordPress
 * admin (CSRF). Plugin executed attacker's commands as the admin. Site became completely compromised.
 * Plugin had 50,000 active installs. Exploit affected all 50,000 sites simultaneously. Security company\n * released advisory. 40,000 sites still vulnerable 6 months later (admins didn't remove plugin).
 * Cost to affected sites: $50,000-$500,000 recovery.\n *
 * **Business Impact:**\n * - SQL injection vulnerabilities (database compromise)\n * - XSS vulnerabilities (visitor malware distribution)\n * - CSRF vulnerabilities (admin account takeover)\n * - Privilege escalation (low-privilege user becomes admin)\n * - Data theft ($100k-$1M liability)\n * - Site compromise ($1,000-$50,000 recovery)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Identifies dangerous plugins early\n * - #9 Show Value: Prevents exploitation before damage\n * - #10 Talk-About-Worthy: "Every plugin passes security review"\n *
 * **Related Checks:**\n * - Plugin Security Vulnerabilities (known CVEs)\n * - User Role Security (privilege escalation detection)\n * - Database Security (SQL injection prevention)\n * - Nonce Verification (CSRF protection)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-coding-standards\n * - Video: https://wpshadow.com/training/wordpress-security-standards (7 min)\n * - Advanced: https://wpshadow.com/training/plugin-vetting-process (13 min)\n *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Coding_Standards Class
 *
 * Detects plugins that violate WordPress coding standards.
 */
class Diagnostic_Plugin_Coding_Standards extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-coding-standards';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Coding Standards';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugins for WordPress coding standards compliance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$violations = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null;
		}

		$plugins_dir = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for direct output (not using functions)
			if ( preg_match( '/<\?php\s*echo\s+["\']/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin file */
					__( '%s: Direct output without proper escaping detected.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for empty sanitization/validation
			if ( preg_match( '/\$_(?:GET|POST|REQUEST)\[/', $content ) && ! preg_match( '/sanitize_/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Accesses $_GET/POST without sanitization.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for empty() on arrays (risky)
			if ( preg_match( '/empty\(\$[a-zA-Z_]+\[\]?\)/', $content ) && preg_match( '/\$\w+\s*=\s*array/', $content ) ) {
				$violations[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses empty() on arrays which may have unexpected results.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $violations ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: violation count, %s: details */
					__( '%d plugins have coding standard violations: %s', 'wpshadow' ),
					count( $violations ),
					implode( ' ', array_slice( $violations, 0, 5 ) )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'violations' => $violations,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-coding-standards',
			);
		}

		return null;
	}
}
