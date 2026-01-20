<?php
/**
 * Permalink Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check permalink structure configuration.
 */
class Diagnostic_Permalinks {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( ! self::is_permalink_configured() ) {
			return array(
				'id'           => 'permalinks-plain',
				'title'        => 'Permalink Structure Not Set',
				'description'  => 'Your site is using plain permalinks (/?p=123). This hurts SEO and user experience. Switch to a prettier structure.',
				'color'        => '#2196f3',
				'bg_color'     => '#e3f2fd',
				'kb_link'      => 'https://wpshadow.com/kb/configure-wordpress-permalinks-for-seo/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=permalinks',
				'action_link'  => admin_url( 'options-permalink.php' ),
				'action_text'  => 'Fix Permalinks',
				'auto_fixable' => true,
				'threat_level' => 30,
			);
		}
		
		return null;
	}
	
	/**
	 * Check if permalinks are properly configured.
	 *
	 * @return bool True if permalinks are set, false if plain.
	 */
	private static function is_permalink_configured() {
		$structure = get_option( 'permalink_structure', '' );
		return ! empty( $structure );
	}
}
