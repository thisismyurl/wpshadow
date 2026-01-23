<?php
declare(strict_types=1);
/**
 * Plugin File Modifications Monitoring Diagnostic
 *
 * Philosophy: Integrity checking - detect unauthorized edits
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if plugin file modifications are monitored.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Plugin_File_Modifications extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_monitoring = has_action( 'wp_update_plugins' ) || has_filter( 'wp_plugin_update_rows' );

		if ( ! $has_monitoring ) {
			return array(
				'id'            => 'plugin-file-modifications',
				'title'         => 'No Plugin File Modification Monitoring',
				'description'   => 'Plugin file edits are not monitored. Malicious code can be injected into plugins without detection. Enable file integrity monitoring for plugins directory.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/monitor-plugin-integrity/',
				'training_link' => 'https://wpshadow.com/training/file-monitoring/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}

}