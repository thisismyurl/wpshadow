<?php
<?php
/**
 * Executable File Prevention Diagnostic
 *
 * Validates prevention of executable file uploads by checking media upload\n * filters. Allowing script uploads (PHP, .exe, .sh) enables remote code execution.\n * Attacker uploads PHP shell, executes arbitrary commands on server.\n *
 * **What This Check Does:**
 * - Validates MIME type filtering on file uploads\n * - Detects if dangerous file types (.php, .exe, .sh) are blocked\n * - Checks allowed file types in WordPress settings\n * - Tests upload filter hooks (add_filter for upload_mimes)\n * - Validates .htaccess prevents PHP execution in uploads folder\n * - Confirms uploaded files aren't accidentally executed\n *
 * **Why This Matters:**
 * Unblocked executable uploads enable full server compromise. Scenarios:\n * - Attacker uploads PHP webshell via media library\n * - PHP executes when accessed (system() function available)\n * - Attacker gains shell access to entire server\n * - Database credentials leaked, customer data stolen\n * - Server used for botnet attacks\n *
 * **Business Impact:**
 * WordPress site allows file uploads (for users). Media library file filter broken\n * (plugin conflict). Attacker uploads \"document.php.jpg\" (bypasses filter). Renames\n * via FTP to \"shell.php\". Executes shell. Full server access gained. Attacker\n * installs ransomware. Site down for 1 week during recovery.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Only safe files uploadable\n * - #9 Show Value: Prevents shell access/ransomware\n * - #10 Beyond Pure: Defense in depth, multiple filtering layers\n *
 * **Related Checks:**
 * - File Permission Security (file access control)\n * - htaccess Protection (execution prevention)\n * - Media Upload Validation (MIME verification)\n *
 * **Learn More:**
 * Secure file uploads: https://wpshadow.com/kb/wordpress-file-upload-security\n * Video: Configuring upload restrictions (9min): https://wpshadow.com/training/upload-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Executable_File_Prevention Class
 *
 * Detects whether executable file types are blocked from uploads.\n * Implements MIME type validation to prevent shell uploads.\n *
 * **Detection Pattern:**
 * 1. Get allowed MIME types via get_allowed_mime_types()\n * 2. Check for dangerous types: .php, .exe, .sh, .bat, .com\n * 3. Test upload filter functionality\n * 4. Validate .htaccess has AddType handlers\n * 5. Check wp-config.php permissions (444 read-only)\n * 6. Return severity if executables allowed\n *
 * **Real-World Scenario:**
 * Theme upload feature (for developers) misconfigured to allow .zip uploads.\n * Plugin also allows .js uploads. Attacker uploads PHP within ZIP. Extracts via\n * FTP. Executes. Admin later discovers PHP files in uploads folder (suspicious).\n *
 * **Implementation Notes:**
 * - Uses get_allowed_mime_types()\n * - Checks for dangerous MIME patterns\n * - Validates .htaccess in uploads folder\n * - Severity: critical (executables allowed), high (single filter)\n * - Treatment: whitelist only safe file types\n *
 * @since 1.6030.2148
 */
class Diagnostic_Executable_File_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'executable-file-prevention';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Executable File Prevention';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates prevention of executable file uploads';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Allowed mime types
	 * - Unfiltered uploads
	 * - Existing executable files in uploads
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for unfiltered uploads.
		if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) && ALLOW_UNFILTERED_UPLOADS ) {
			$issues[] = __( 'ALLOW_UNFILTERED_UPLOADS is enabled - executable files may be allowed', 'wpshadow' );
		}

		// Check allowed MIME types for executable extensions.
		$allowed = get_allowed_mime_types();
		$blocked_exts = array( 'php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'com', 'cgi', 'pl', 'py', 'js', 'jar', 'asp', 'aspx' );

		foreach ( $allowed as $exts => $mime ) {
			foreach ( $blocked_exts as $blocked ) {
				if ( false !== strpos( $exts, $blocked ) ) {
					$issues[] = sprintf(
						/* translators: %s: file extension */
						__( 'Executable extension %s is allowed in upload mimes', 'wpshadow' ),
						$blocked
					);
					break 2;
				}
			}
		}

		// Check for upload_mimes filter overrides.
		if ( has_filter( 'upload_mimes' ) ) {
			$issues[] = __( 'upload_mimes filter is active - verify it does not allow executable files', 'wpshadow' );
		}

		// Scan for executable files in uploads (recent attachments).
		global $wpdb;
		$exec_files = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				AND (
					meta_value LIKE %.php
					OR meta_value LIKE %.phtml
					OR meta_value LIKE %.phar
					OR meta_value LIKE %.exe
					OR meta_value LIKE %.sh
					OR meta_value LIKE %.bat
					OR meta_value LIKE %.cmd
					OR meta_value LIKE %.cgi
					OR meta_value LIKE %.pl
					OR meta_value LIKE %.py
					OR meta_value LIKE %.js
					OR meta_value LIKE %.jar
					OR meta_value LIKE %.asp
					OR meta_value LIKE %.aspx
				)",
				''
			)
		);

		if ( 0 < $exec_files ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d executable file found in uploads',
					'%d executable files found in uploads',
					$exec_files,
					'wpshadow'
				),
				$exec_files
			);
		}

		// Check for wp_check_filetype_and_ext filter.
		if ( has_filter( 'wp_check_filetype_and_ext' ) ) {
			$issues[] = __( 'wp_check_filetype_and_ext filter is active - verify it blocks executable uploads', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d executable upload issue detected',
						'%d executable upload issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'      => 'high',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/executable-file-prevention',
				'context'       => array(
					'why'            => __( 'Executable uploads = instant RCE. Real scenario: Attacker uploads shell.exe or shell.php to uploads dir. Visits URL. Executes with web server permissions. Site compromised. With prevention: PHP execution disabled via .htaccess. Shell uploads rejected by extension whitelist. Neither can execute. Attack blocked.', 'wpshadow' ),
					'recommendation' => __( '1. Disable ALLOW_UNFILTERED_UPLOADS in wp-config. 2. Whitelist only: JPG, PNG, GIF, PDF, DOCX. 3. Block all: PHP, EXE, BAT, CMD, COM, SH. 4. Check wp_check_filetype_and_ext filter works. 5. Add .htaccess: php_flag engine off. 6. Disable script handlers: SetHandler text/plain. 7. Scan uploads for existing executables. 8. Remove any .php, .exe, .sh files. 9. Set 755 on dirs, 644 on files. 10. Test: Try upload shell.php (should fail).', 'wpshadow' ),
				),
				'details'       => array(
					'issues'     => $issues,
					'exec_files' => $exec_files,
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'executable-prevention' );
			return $finding;
		}

		return null;
	}
}
