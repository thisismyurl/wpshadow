<?php
/**
 * Diagnostic: File Editing Disabled
 *
 * Checks if WordPress theme and plugin file editing is disabled via DISALLOW_FILE_EDIT.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_Editing Class
 *
 * Detects if the built-in WordPress theme and plugin file editors are
 * enabled in the WordPress admin panel. These editors should be disabled
 * on production sites because they allow direct code injection if an
 * administrator account is compromised.
 *
 * If an attacker gains access to an admin account, they can use the file
 * editor to inject malicious code directly into theme or plugin files,
 * creating a persistent backdoor without any server-level audit trail.
 *
 * Professional sites should disable the file editor and use version control
 * (Git) and SFTP for code changes, not the browser-based editor.
 *
 * The fix is simple: add `define( 'DISALLOW_FILE_EDIT', true );` to wp-config.php
 *
 * @since 1.2601.2200
 */
class Diagnostic_File_Editing extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'file-editing';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'File Editing Enabled';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Detects if WordPress file editor is enabled, risking code injection attacks';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if DISALLOW_FILE_EDIT is defined and set to true.
	 * If not defined or set to false, the file editor is enabled.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if file editing is enabled, null if disabled.
	 */
	public static function check() {
		$file_edit_disabled = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;

		if ( ! $file_edit_disabled ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'WordPress file editing is enabled. If an admin account is compromised, attackers can inject malicious code directly into theme or plugin files. Disable this immediately by adding define( \'DISALLOW_FILE_EDIT\', true ); to wp-config.php.',
					'wpshadow'
				),
				'severity'           => 'high',
				'threat_level'       => 70,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/security-file-editing',
				'family'             => self::$family,
				'details'            => array(
					'disallow_file_edit' => false,
					'file_editor_enabled' => true,
					'risk'               => 'High - attackers can inject code if admin account compromised',
					'fix'                => "Add to wp-config.php: define( 'DISALLOW_FILE_EDIT', true );",
				),
			);
		}

		// File editing is properly disabled
		return null;
	}
}
