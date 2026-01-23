<?php
declare(strict_types=1);
/**
 * Insecure Deserialization Diagnostic
 *
 * Philosophy: Code injection - prevent object injection
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
class Diagnostic_Insecure_Deserialization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			if ( preg_match( '/\bunserialize\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)/', $content ) ) {
				return array(
					'id'          => 'insecure-deserialization',
					'title'       => 'Insecure Deserialization Detected',
					'description' => 'Code uses unserialize() on user-controlled input. This allows object injection attacks. Use json_decode() instead.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/fix-insecure-unserialize/',
					'training_link' => 'https://wpshadow.com/training/object-injection/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
	}

}