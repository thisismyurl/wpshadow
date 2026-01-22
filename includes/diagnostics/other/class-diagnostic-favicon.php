<?php
declare(strict_types=1);
/**
 * Favicon / Site Icon Diagnostic
 *
 * Philosophy: Small UX trust signal; educates on branding consistency.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if a site icon (favicon) is set.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Favicon extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			return null; // Favicon set
		}

		return array(
			'id'           => 'favicon',
			'title'        => 'No Site Icon (Favicon) Set',
			'description'  => 'Adding a site icon improves brand trust and recognition in browser tabs, bookmarks, and mobile devices.',
			'severity'     => 'low',
			'category'     => 'design',
			'kb_link'      => 'https://wpshadow.com/kb/add-wordpress-site-icon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=favicon',
			'auto_fixable' => false,
			'threat_level' => 15,
		);
	}
}
