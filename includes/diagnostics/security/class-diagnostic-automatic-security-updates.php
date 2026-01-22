<?php
declare(strict_types=1);
/**
 * Automatic Security Updates Diagnostic
 *
 * Philosophy: Patch management - automatic core/plugin updates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if automatic security updates are enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Automatic_Security_Updates extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$auto_updates = get_option( 'auto_update_core_dev' ) || get_option( 'auto_update_core_minor' ) || get_option( 'auto_update_plugins' );
		
		if ( ! $auto_updates ) {
			return array(
				'id'          => 'automatic-security-updates',
				'title'       => 'No Automatic Security Updates',
				'description' => 'Security patches are not applied automatically. Unpatched vulnerabilities are exploited before you manually update. Enable automatic security updates for core, plugins, and themes.',
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-automatic-updates/',
				'training_link' => 'https://wpshadow.com/training/update-management/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}
