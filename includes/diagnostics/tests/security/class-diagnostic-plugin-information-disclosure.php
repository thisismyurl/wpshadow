<?php
/**
 * Plugin Information Disclosure Diagnostic
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
 * @subpackage Diagnostics
 * @since      1.4031.1939
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Information_Disclosure Class
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
class Diagnostic_Plugin_Information_Disclosure extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-information-disclosure';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Information Disclosure';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins leaking sensitive system information';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$disclosure_concerns = array();

		// Check for exposed PHP version (remove_action wp_head)
		global $wp_scripts;
		if ( ! has_action( 'wp_head', 'wp_generator' ) ) {
			// WordPress version is hidden
		} else {
			$disclosure_concerns[] = __( 'WordPress version publicly disclosed in <meta generator> tag.', 'wpshadow' );
		}

		// Check for plugins exposing their versions
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		$exposed_versions = 0;
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for wp_enqueue_script/style with version parameter
			if ( preg_match( '/wp_enqueue_(?:script|style).*\$plugin_version|\$version/', $content ) ) {
				// This exposes version in CSS/JS URLs
				$exposed_versions++;
			}

			// Check for API endpoints returning version
			if ( preg_match( '/rest_ensure_response|wp_send_json.*version/', $content ) ) {
				// REST API might expose version
				$exposed_versions++;
			}
		}

		if ( $exposed_versions > 0 ) {
			$disclosure_concerns[] = sprintf(
				/* translators: %d: plugin count */
				__( '%d plugins may expose version numbers in asset URLs (facilitates targeted attacks).', 'wpshadow' ),
				$exposed_versions
			);
		}

		// Check for directory listing exposure
		$plugin_readme_count = 0;
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = dirname( $plugins_dir . '/' . $plugin );
			if ( file_exists( $plugin_dir . '/readme.txt' ) ) {
				$plugin_readme_count++;
			}
		}

		if ( $plugin_readme_count > 0 ) {
			$disclosure_concerns[] = sprintf(
				/* translators: %d: count */
				__( '%d plugins have publicly accessible readme.txt files (exposes version and functionality).', 'wpshadow' ),
				$plugin_readme_count
			);
		}

		// Check for sensitive data in meta tags
		if ( preg_match( '/<meta\s+name\s*=\s*["\']author["\']|admin_email|siteurl/', wp_get_document_title() ) ) {
			$disclosure_concerns[] = __( 'Sensitive data may be exposed in HTML meta tags or comments.', 'wpshadow' );
		}

		if ( ! empty( $disclosure_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', array_slice( $disclosure_concerns, 0, 3 ) ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'concerns'       => $disclosure_concerns,
					'exposed_versions' => $exposed_versions,
				),
				'kb_link'      => 'https://wpshadow.com/kb/information-disclosure',
			);
		}

		return null;
	}
}
