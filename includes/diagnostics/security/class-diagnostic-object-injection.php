<?php
declare(strict_types=1);
/**
 * Object Injection Protection Diagnostic
 *
 * Philosophy: Code security - detect unsafe deserialization
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for unsafe unserialize() usage.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Object_Injection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan plugins directory for unserialize patterns
		$plugins_dir = WP_PLUGIN_DIR;
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_files = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_dir = dirname( $plugins_dir . '/' . $plugin );
			if ( ! is_dir( $plugin_dir ) ) {
				continue;
			}
			
			// Simple scan of main plugin file only (full scan would be too intensive)
			$plugin_file = $plugins_dir . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				// Look for unsafe unserialize on user input
				if ( preg_match( '/unserialize\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/i', $content ) ) {
					$vulnerable_files[] = basename( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_files ) ) {
			return array(
				'id'          => 'object-injection',
				'title'       => 'Potential Object Injection Vulnerability',
				'description' => sprintf(
					'Detected unsafe unserialize() usage in: %s. This can lead to remote code execution. Contact plugin authors or replace with secure alternatives.',
					implode( ', ', $vulnerable_files )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-object-injection/',
				'training_link' => 'https://wpshadow.com/training/object-injection-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}
