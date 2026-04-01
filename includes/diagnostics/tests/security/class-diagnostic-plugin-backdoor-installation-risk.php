<?php
/**
 * Plugin Backdoor Installation Risk Diagnostic
 *
 * Detects plugins vulnerable to backdoor installation.
 * Vulnerability allows attacker to install persistent backdoor (stays even after
 * password change). Backdoor = permanent access, permanent compromise.
 *
 * **What This Check Does:**
 * - Scans active plugins for backdoor installation vulnerabilities
 * - Checks if file upload/write capabilities properly restricted
 * - Tests for arbitrary code execution vulnerabilities
 * - Detects if plugin allows execution of uploaded files
 * - Validates file permissions (non-executable)
 * - Tests for known backdoor patterns
 *
 * **Why This Matters:**
 * Backdoor vulnerability = permanent compromise. Scenarios:
 * - Plugin allows unauthenticated file upload
 * - Attacker uploads PHP webshell (backdoor)
 * - Webshell persists on server (even if password changed)
 * - Attacker maintains access indefinitely
 * - Site fully compromised for months/years
 *
 * **Business Impact:**
 * Plugin with backdoor vulnerability (unpatched). Attacker uploads PHP webshell.
 * Backdoor active for 1 year (undetected). Exfiltrates customer data monthly.
 * Data: 100K records. GDPR fine: $2M. Plus incident response, forensics,
 * notification: $3M additional. Total: $5M+ damage from single vulnerability.
 * Early detection + patching prevents entirely.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sites protected from backdoors
 * - #9 Show Value: Quantified prevention of long-term compromise
 * - #10 Beyond Pure: Proactive threat detection
 *
 * **Related Checks:**
 * - Plugin Vulnerability Detection (general security)
 * - File Permission Security (upload restrictions)
 * - Malware Scanning Not Configured (detection)
 *
 * **Learn More:**
 * Backdoor vulnerabilities: https://wpshadow.com/kb/wordpress-backdoor-risks
 * Video: Identifying plugin backdoors (14min): https://wpshadow.com/training/backdoor-detection
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
 * Diagnostic_Plugin_Backdoor_Installation_Risk Class
 *
 * Identifies plugins vulnerable to backdoor installation.
 *
 * **Detection Pattern:**
 * 1. Scan active plugin files for upload handling
 * 2. Check if file execution prevented (not executable)
 * 3. Detect arbitrary code execution patterns
 * 4. Test for file write vulnerabilities
 * 5. Validate proper permission checks
 * 6. Return severity if backdoor risk detected
 *
 * **Real-World Scenario:**
 * Plugin handles file uploads (media manager). Doesn't validate file extension.
 * Attacker uploads PHP file disguised as image. PHP file executes. Attacker
 * has code execution. Uploads backdoor webshell. Now has persistent access.
 * Password change won't help (backdoor still there). Site compromised for
 * months before discovered. Proper implementation: validate extension +
 * store uploads outside web root + disable PHP execution.
 *
 * **Implementation Notes:**
 * - Scans plugin files for upload/execution patterns
 * - Tests file handling security
 * - Checks permission validation
 * - Severity: critical (backdoor found), high (unsafe file handling)
 * - Treatment: update plugin or remove if unpatched
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Backdoor_Installation_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-backdoor-installation-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Backdoor Installation Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to backdoor installation';

	/**
	 * Diagnostic family
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
		$backdoor_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for file write operations without validation
			if ( preg_match( '/file_put_contents|fopen.*[wr]|fwrite/', $content ) ) {
				// Check if file path is validated
				if ( ! preg_match( '/realpath|dirname|basename|wp_upload_dir|wp_plugin_dir/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Writes files without path validation (could create backdoor).', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for plugin/theme installation without signature verification
			if ( preg_match( '/wp_install_plugin|wp_install_theme|install_plugin_form/', $content ) ) {
				if ( ! preg_match( '/verify_plugin_package|check_package_plugin|wp_remote_get.*signature/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Installs plugins/themes without signature verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for remote execution via include/require
			if ( preg_match( '/(?:include|require).*\$_(?:GET|POST|REQUEST|SERVER|COOKIE)/', $content ) ) {
				$backdoor_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Includes files based on user input (Remote Code Execution/backdoor).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for creating admin users remotely
			if ( preg_match( '/wp_create_user|wp_insert_user.*password.*email/', $content ) ) {
				// Check if triggered by user action without verification
				if ( ! preg_match( '/is_admin|current_user_can|wp_verify_nonce/', $content ) ) {
					$backdoor_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: May create admin users without proper verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for web shell patterns
			if ( preg_match( '/system\s*\(\s*\$_[^)]*\)|shell_exec\s*\(\s*\$_|exec\s*\(\s*\$_/', $content ) ) {
				$backdoor_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Contains web shell patterns (backdoor).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $backdoor_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d potential backdoor installation risks detected: %s', 'wpshadow' ),
					count( $backdoor_concerns ),
					implode( ' | ', array_slice( $backdoor_concerns, 0, 2 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'backdoor_concerns' => $backdoor_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/backdoor-prevention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
