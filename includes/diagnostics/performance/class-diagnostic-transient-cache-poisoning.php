<?php
declare(strict_types=1);
/**
 * Transient Cache Poisoning Diagnostic
 *
 * Philosophy: Cache security - prevent poisoning attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for cache poisoning via user-controlled transient keys.
 */
class Diagnostic_Transient_Cache_Poisoning extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan active plugins for dangerous caching patterns
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_plugins = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				
				// Look for set_transient with user input as key
				if ( preg_match( '/set_transient\s*\(\s*[\'"]?[^\'"\)]*\$_(GET|POST|REQUEST)\[/i', $content ) ||
				     preg_match( '/set_transient\s*\(\s*\$_(GET|POST|REQUEST)\[/i', $content ) ) {
					$vulnerable_plugins[] = dirname( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_plugins ) ) {
			return array(
				'id'          => 'transient-cache-poisoning',
				'title'       => 'Transient Cache Poisoning Risk',
				'description' => sprintf(
					'Plugins using user input in transient keys: %s. Attackers can fill cache with garbage or poison cached data. Hash/sanitize user input before using as transient key.',
					implode( ', ', $vulnerable_plugins )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-cache-poisoning/',
				'training_link' => 'https://wpshadow.com/training/cache-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
