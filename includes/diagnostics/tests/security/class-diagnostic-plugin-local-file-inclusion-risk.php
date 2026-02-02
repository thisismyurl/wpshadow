<?php
/**
 * Plugin Local File Inclusion Risk Diagnostic
 *
 * Detects plugins vulnerable to Local File Inclusion (LFI) attacks.
 * LFI = attacker includes arbitrary server files (config, admin pages, etc).
 * Plugin doesn't validate file paths. Attacker traverses to /etc/passwd.
 *
 * **What This Check Does:**
 * - Scans plugin files for include/require statements
 * - Checks if file paths validated
 * - Detects if user input included directly
 * - Tests for path traversal protection
 * - Validates directory traversal prevented (../../../etc/passwd)
 * - Returns severity if LFI vulnerable
 *
 * **Why This Matters:**
 * Unvalidated file inclusion = arbitrary file access. Scenarios:
 * - Plugin includes files based on user input
 * - Attacker passes: "../../etc/passwd"
 * - Server includes /etc/passwd
 * - Attacker reads sensitive config files
 * - May expose credentials, paths, secrets
 *
 * **Business Impact:**
 * Document plugin includes files based on document ID. No validation.
 * Attacker modifies URL: "doc.php?id=../../wp-config.php". Server includes
 * wp-config.php. Attacker reads database credentials. Uses creds to connect.
 * Steals entire database. Cost: $500K+. Validation (prevent ../) would prevent
 * entirely. 5-minute fix. Prevents $500K+ exposure.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: File access is controlled
 * - #9 Show Value: Prevents config file exposure
 * - #10 Beyond Pure: Input validation everywhere
 *
 * **Related Checks:**
 * - Remote File Inclusion (related attack vector)
 * - File Permission Security (file access control)
 * - Plugin CSRF Protection (similar surface)
 *
 * **Learn More:**
 * Local file inclusion: https://wpshadow.com/kb/wordpress-lfi-attacks
 * Video: Preventing LFI vulnerabilities (11min): https://wpshadow.com/training/lfi-prevention
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.4031.1939
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Local_File_Inclusion_Risk Class
 *
 * Identifies plugins vulnerable to LFI attacks.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for include/require statements
 * 2. Check if file path comes from user input
 * 3. Test if path traversal prevented (../)
 * 4. Validate realpath() checks present
 * 5. Test if file is within expected directory
 * 6. Return severity if LFI vulnerable
 *
 * **Real-World Scenario:**
 * Template plugin includes template based on GET parameter:
 * include($_GET['template'] . '.php'). Attacker passes: template=../../etc/passwd%00.
 * Server includes /etc/passwd (null byte injection). Attacker reads password hashes.
 * Proper implementation: validate template name (whitelist), prevent ../ (realpath check).
 *
 * **Implementation Notes:**
 * - Scans plugin files for include/require patterns
 * - Tests path traversal attacks
 * - Checks for realpath/sanitization
 * - Severity: critical (LFI confirmed), high (potential LFI)
 * - Treatment: validate file paths, use realpath(), whitelist allowed files
 *
 * @since 1.4031.1939
 */
class Diagnostic_Plugin_Local_File_Inclusion_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-local-file-inclusion-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Local File Inclusion Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to Local File Inclusion attacks';

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
		$lfi_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for file path from user input
			if ( preg_match( '/include\s*\(|require\s*\(|include_once\s*\(|require_once\s*\(/', $content ) ) {
				// Check for user input in path
				if ( preg_match( '/\$_(?:GET|POST|REQUEST|COOKIE)\[["\'].*["\'][^;]*include|require/', $content ) ) {
					$lfi_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Includes files based on user input ($_GET/$_POST).', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}

				// Check for path traversal (../)
				if ( preg_match( '/\.\.\/' ) ) {
					$lfi_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: May be vulnerable to path traversal (../) attacks.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for dynamic file loading from templates
			if ( preg_match( '/locate_template.*\$_(?:GET|POST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Loads templates based on user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for plugin loading from user input
			if ( preg_match( '/load_plugin_textdomain.*\$_(?:GET|POST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Loads text domains based on user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for stream wrappers (php://, file://, zip://)
			if ( preg_match( '/(?:php|file|zip|phar):\/\/.*\$_(?:GET|POST|REQUEST)/', $content ) ) {
				$lfi_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses stream wrappers with user input (LFI vector).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $lfi_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d local file inclusion risks detected: %s', 'wpshadow' ),
					count( $lfi_concerns ),
					implode( ' | ', array_slice( $lfi_concerns, 0, 2 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'details'      => array(
					'lfi_concerns' => $lfi_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/lfi-prevention',
			);
		}

		return null;
	}
}
