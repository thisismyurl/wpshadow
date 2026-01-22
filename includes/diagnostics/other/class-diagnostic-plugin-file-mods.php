<?php
declare(strict_types=1);
/**
 * Plugin File Modifications Diagnostic
 *
 * Philosophy: Security hardening - prevent code injection via admin
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if plugin/theme modifications are blocked.
 */
class Diagnostic_Plugin_File_Mods extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if DISALLOW_FILE_MODS is enabled
		if ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) {
			return array(
				'id'            => 'plugin-file-mods',
				'title'         => 'Plugin/Theme Installation Allowed',
				'description'   => 'Admins can install plugins and themes via the dashboard. For production sites, consider defining DISALLOW_FILE_MODS to prevent unauthorized installations.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-file-modifications/',
				'training_link' => 'https://wpshadow.com/training/file-mod-security/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
	}
}
