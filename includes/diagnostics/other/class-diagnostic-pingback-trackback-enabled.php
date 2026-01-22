<?php
declare(strict_types=1);
/**
 * Pingback/Trackback Enabled Diagnostic
 *
 * Philosophy: Legacy features - disable unnecessary endpoints
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if pingback/trackback is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Pingback_Trackback_Enabled extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( get_option( 'default_ping_status' ) === 'open' ) {
			return array(
				'id'            => 'pingback-trackback-enabled',
				'title'         => 'Pingback/Trackback Enabled',
				'description'   => 'Pingbacks/Trackbacks are old features rarely used. They are exploited for SSRF attacks and amplification attacks. Disable: Settings > Discussion > disable pingbacks.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-pingbacks/',
				'training_link' => 'https://wpshadow.com/training/legacy-features/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
			);
		}

		return null;
	}
}
