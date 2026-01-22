<?php
declare(strict_types=1);
/**
 * Vulnerable jQuery Version Diagnostic
 *
 * Philosophy: Library security - update jQuery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for vulnerable jQuery versions.
 */
class Diagnostic_Vulnerable_jQuery extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts;
		
		if ( empty( $wp_scripts->registered['jquery'] ) ) {
			return null;
		}
		
		$jquery = $wp_scripts->registered['jquery'];
		
		// Check version - vulnerable versions: < 1.12.4, 2.x < 2.2.4, 3.x < 3.0.0
		if ( preg_match( '/(\d+)\.(\d+)\.(\d+)/', $jquery->ver, $matches ) ) {
			$major = intval( $matches[1] );
			$minor = intval( $matches[2] );
			
			if ( ( $major === 1 && $minor < 12 ) || ( $major === 2 && $minor < 2 ) ) {
				return array(
					'id'          => 'vulnerable-jquery',
					'title'       => 'Vulnerable jQuery Version',
					'description' => sprintf(
						'jQuery version %s has known security vulnerabilities. Update to latest 3.x version.',
						$jquery->ver
					),
					'severity'    => 'high',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/update-jquery/',
					'training_link' => 'https://wpshadow.com/training/library-updates/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
		}
		
		return null;
	}
}
