<?php declare(strict_types=1);
/**
 * Pingback/Trackback Enabled Diagnostic
 *
 * Philosophy: Legacy features - disable unnecessary endpoints
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if pingback/trackback is enabled.
 */
class Diagnostic_Pingback_Trackback_Enabled {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( get_option( 'default_ping_status' ) === 'open' ) {
			return array(
				'id'          => 'pingback-trackback-enabled',
				'title'       => 'Pingback/Trackback Enabled',
				'description' => 'Pingbacks/Trackbacks are old features rarely used. They are exploited for SSRF attacks and amplification attacks. Disable: Settings > Discussion > disable pingbacks.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-pingbacks/',
				'training_link' => 'https://wpshadow.com/training/legacy-features/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
