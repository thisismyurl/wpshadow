<?php declare(strict_types=1);
/**
 * Inactive Theme Security Diagnostic
 *
 * Philosophy: Reduce attack surface - unused themes are still exploitable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for inactive themes with known vulnerabilities.
 */
class Diagnostic_Inactive_Themes {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$all_themes = wp_get_themes();
		$active_theme = wp_get_theme();
		$inactive_themes = array();
		
		foreach ( $all_themes as $theme_slug => $theme ) {
			if ( $theme->get_stylesheet() !== $active_theme->get_stylesheet() ) {
				$inactive_themes[] = $theme->get( 'Name' );
			}
		}
		
		if ( count( $inactive_themes ) > 3 ) {
			return array(
				'id'          => 'inactive-themes',
				'title'       => 'Excessive Inactive Themes',
				'description' => sprintf(
					'You have %d inactive themes installed. Unused themes can still be exploited if they have vulnerabilities. Remove themes you don\'t need: %s',
					count( $inactive_themes ),
					implode( ', ', array_slice( $inactive_themes, 0, 3 ) )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/remove-unused-themes/',
				'training_link' => 'https://wpshadow.com/training/theme-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
