<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: File Edit Disabled Check
 *
 * Checks if DISALLOW_FILE_EDIT is properly set for security.
 * This is a sample implemented diagnostic for CI testing.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_File_Edit_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = 'file-edit-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Edit Disabled Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file editing is disabled in wp-config.php for security hardening.';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if DISALLOW_FILE_EDIT is defined and set to true
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT === true ) {
			return null; // Pass - file editing is disabled
		}

		// File editing is enabled (security concern)
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'File editing in wp-admin is enabled. This allows attackers to modify plugin and theme files if they gain access to your admin area.',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/file-edit-disabled/',
			'training_link' => 'https://wpshadow.com/training/file-edit-disabled/',
			'auto_fixable'  => false,
			'threat_level'  => 85,
		);
	}
}
