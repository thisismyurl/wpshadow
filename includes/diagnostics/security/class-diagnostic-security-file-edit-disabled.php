<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: File Edit Permissions Check
 *
 * Checks if DISALLOW_FILE_EDIT is properly configured for security hardening.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */
class Diagnostic_Security_File_Edit_Disabled extends Diagnostic_Base {
	protected static $slug = 'security-file-edit-disabled';
	protected static $title = 'File Edit Permissions';
	protected static $description = 'Checks if file editing is properly disabled in wp-config.php for security.';
	protected static $family = 'security';
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(): ?array {
		// Check if file editing is not disabled (security issue)
		if (!Diagnostic_Lean_Checks::security_basics_issue()) {
			return null; // No issue
		}

		// Issue found: file editing is not properly disabled
		return Diagnostic_Lean_Checks::build_finding(
			static::$slug,
			static::$title,
			'File editing in WordPress admin is enabled. For security hardening, add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.',
			static::$family,
			'medium',
			65,
			static::$slug
		);
	}
}
