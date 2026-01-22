<?php declare(strict_types=1);
/**
 * WP_Filesystem Arbitrary File Write Diagnostic
 *
 * Philosophy: Filesystem security - validate file paths
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for arbitrary file write via WP_Filesystem.
 */
class Diagnostic_WP_Filesystem_Arbitrary_Write {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Scan active plugins for dangerous WP_Filesystem patterns
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_plugins = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				
				// Look for WP_Filesystem put_contents with user input
				if ( preg_match( '/\$wp_filesystem->put_contents\s*\([^,]*\$_(GET|POST|REQUEST)\[/i', $content ) ||
				     preg_match( '/WP_Filesystem.*put_contents.*\$_(GET|POST|REQUEST)/is', $content ) ) {
					$vulnerable_plugins[] = dirname( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_plugins ) ) {
			return array(
				'id'          => 'wp-filesystem-arbitrary-write',
				'title'       => 'WP_Filesystem Arbitrary File Write Risk',
				'description' => sprintf(
					'Plugins with unsafe WP_Filesystem usage: %s. User-controlled file paths in put_contents() allow arbitrary file write, enabling remote code execution. Validate paths against ABSPATH.',
					implode( ', ', $vulnerable_plugins )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-wp-filesystem/',
				'training_link' => 'https://wpshadow.com/training/filesystem-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		return null;
	}
}
