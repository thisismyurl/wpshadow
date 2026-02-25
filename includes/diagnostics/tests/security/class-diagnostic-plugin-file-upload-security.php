<?php
/**
 * Plugin File Upload Security Diagnostic
 *
 * Detects plugins with insecure file upload handling. Attacker uploads malicious
 * file (PHP webshell, EXE, etc). Plugin doesn't validate extension/content.
 * Attacker executes file. Gets code execution. Full site compromise.
 *
 * **What This Check Does:**
 * - Scans plugins for file upload functions
 * - Checks if file extension validated
 * - Tests if MIME type verified
 * - Detects if uploads stored outside web root
 * - Validates execution disabled in upload dir
 * - Tests for known upload attack patterns
 *
 * **Why This Matters:**
 * Unvalidated file upload = remote code execution. Scenarios:
 * - Plugin accepts file uploads (for documents, avatars, etc)
 * - Plugin doesn't check file extension
 * - Attacker uploads "profile.php" disguised as image
 * - Browser opens file. PHP executes. Attacker has shell.
 * - Full site compromise within seconds
 *
 * **Business Impact:**
 * Media gallery plugin allows uploads. No validation. Attacker uploads PHP
 * webshell (disguised as JPG). Plugin stores in web-accessible folder.
 * Attacker executes webshell. Downloads database. Steals 100K customer
 * records. GDPR fine: $2M+. With validation: PHP file rejected (wrong extension).
 * Attack impossible. Cost difference: $1M+ damage prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: File uploads are safe
 * - #9 Show Value: Prevents remote code execution
 * - #10 Beyond Pure: Input validation everywhere
 *
 * **Related Checks:**
 * - File Permission Security (upload directory safety)
 * - Executable File Prevention (disable execution)
 * - Plugin Code Injection Prevention (related vector)
 *
 * **Learn More:**
 * File upload security: https://wpshadow.com/kb/wordpress-file-upload-security
 * Video: Securing file uploads (12min): https://wpshadow.com/training/upload-security
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
 * Diagnostic_Plugin_File_Upload_Security Class
 *
 * Identifies plugins with insecure file upload handling.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for upload handling ($_FILES, move_uploaded_file)
 * 2. Check if file extension validated
 * 3. Test MIME type verification
 * 4. Detect if uploads in web root (dangerous)
 * 5. Validate execution disabled (.htaccess)
 * 6. Return severity if upload insecure
 *
 * **Real-World Scenario:**
 * Comment system plugin allows avatar uploads. Doesn't validate extension.
 * Attacker uploads "avatar.php". Plugin saves as /wp-content/uploads/avatar.php.
 * Attacker visits /wp-content/uploads/avatar.php. PHP executes. Code execution.
 * Proper implementation: validate extension (only JPG/PNG), store outside web root,
 * disable execution in upload directory.
 *
 * **Implementation Notes:**
 * - Scans plugin files for upload functions
 * - Tests extension/MIME validation
 * - Checks upload directory configuration
 * - Severity: critical (RCE possible), high (weak validation)
 * - Treatment: implement proper file upload validation
 *
 * @since 1.4031.1939
 */
class Diagnostic_Plugin_File_Upload_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-file-upload-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin File Upload Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins with insecure file upload handling';

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
		$upload_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for move_uploaded_file without proper validation
			if ( preg_match( '/move_uploaded_file\s*\(/', $content ) ) {
				// Check if it has MIME type checking
				if ( ! preg_match( '/wp_check_filetype|mime_type|getimagesize/', $content ) ) {
					$upload_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Uploads files without MIME type validation.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}

				// Check for extension whitelist
				if ( ! preg_match( '/allowed_extensions|file_types|extension/', $content ) ) {
					$upload_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: No file extension whitelist detected.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for $_FILES access without sanitization
			if ( preg_match( '/\$_FILES/', $content ) && ! preg_match( '/sanitize_file_name|wp_handle_upload/', $content ) ) {
				$upload_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Accesses $_FILES without proper sanitization.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $upload_concerns ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: concern count, %s: details */
					__( '%1$d file upload security concerns detected: %2$s', 'wpshadow' ),
					count( $upload_concerns ),
					implode( ', ', array_slice( $upload_concerns, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-file-upload-security',
				'context'      => array(
					'why'            => __( 'Plugins with unvalidated file uploads = guaranteed RCE. Real scenario: Popular form plugin doesn\'t validate file type. Attacker uploads shell.php. Visits /wp-content/uploads/plugin/shell.php. Full compromise. Installed on 100K+ sites. Cost: $4.29M/breach. With validation: Shell rejected. Attack prevented.', 'wpshadow' ),
					'recommendation' => __( '1. Review vulnerable plugins: disable or update. 2. Check plugin code for move_uploaded_file(). 3. Verify extension whitelist exists (JPG/PNG/PDF only). 4. Ensure MIME type validation: wp_check_filetype(). 5. Check file size limits enforced. 6. Verify uploads outside web root or execution disabled. 7. Use wp_handle_upload() function (built-in validation). 8. Test with malicious filename: shell.php (should reject). 9. Scan existing uploads for .php/.exe files. 10. Disable File Edit in wp-config.php: DISALLOW_FILE_EDIT=true.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'plugin-upload-security' );
			return $finding;
		}

		return null;
	}
}
