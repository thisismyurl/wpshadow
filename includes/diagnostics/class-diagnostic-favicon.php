<?php declare(strict_types=1);
/**
 * Favicon / Site Icon Diagnostic
 *
 * Philosophy: Small UX trust signal; educates on branding consistency.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if a site icon (favicon) is set.
 */
class Diagnostic_Favicon {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			return null; // Favicon set
		}
		
		return array(
			'title'       => 'No Site Icon (Favicon) Set',
			'description' => 'Adding a site icon improves brand trust and recognition in browser tabs, bookmarks, and mobile devices.',
			'severity'    => 'low',
			'category'    => 'design',
			'kb_link'     => 'https://wpshadow.com/kb/add-wordpress-site-icon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=favicon',
			'auto_fixable' => false,
			'threat_level' => 15,
		);
	}
}
