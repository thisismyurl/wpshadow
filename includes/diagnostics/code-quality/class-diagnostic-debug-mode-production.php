<?php
declare(strict_types=1);
/**
 * Debug Mode in Production Diagnostic
 *
 * Philosophy: Information disclosure - disable debug output
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if debug mode is disabled in production.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Debug_Mode_Production extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'          => 'debug-mode-production',
				'title'       => 'WordPress Debug Mode Enabled in Production',
				'description' => 'WP_DEBUG is enabled. This displays error details revealing system paths and database structure to attackers. Disable debug mode: set WP_DEBUG to false.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-debug-mode/',
				'training_link' => 'https://wpshadow.com/training/debugging-safely/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
