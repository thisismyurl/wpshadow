<?php
declare(strict_types=1);
/**
 * Theme/Plugin Modification Detection Diagnostic
 *
 * Philosophy: Change detection - alert on core file modifications
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if theme/plugin modifications are monitored.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Theme_Plugin_Modification extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_monitoring = has_action( 'activated_plugin' ) && has_action( 'deactivated_plugin' );
		
		if ( ! $has_monitoring ) {
			return array(
				'id'          => 'theme-plugin-modification',
				'title'       => 'No Theme/Plugin Change Monitoring',
				'description' => 'Theme and plugin modifications are not monitored. Malicious changes to core files go undetected. Enable file change detection.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/monitor-plugin-changes/',
				'training_link' => 'https://wpshadow.com/training/file-modification-detection/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
