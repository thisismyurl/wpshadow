<?php
/**
 * Site Tagline Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Check if site tagline/description is set.
 */
class Diagnostic_Tagline {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( empty( get_bloginfo( 'description' ) ) ) {
			$is_registered = self::is_site_registered();
			
			$finding = array(
				'id'           => 'tagline-empty',
				'title'        => 'Site Tagline is Empty',
				'description'  => 'Add a tagline (Settings → General) to improve SEO and help visitors understand your site quickly.' . ( ! $is_registered ? ' 💡 Register with WPShadow and our free AI Support Guardian can recommend the perfect tagline for your site!' : '' ),
				'color'        => '#2196f3',
				'bg_color'     => '#e3f2fd',
				'kb_link'      => 'https://wpshadow.com/kb/write-an-effective-site-tagline/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline',
				'modal_trigger' => 'wpshadow-tagline-modal',
				'action_text'  => 'Add Tagline',
				'auto_fixable' => false,
				'threat_level' => 20,
			);
			
			// Only show AI button for unregistered sites
			if ( ! $is_registered ) {
				$finding['secondary_action_link'] = 'https://wpshadow.com/register/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline';
				$finding['secondary_action_text'] = 'Get AI Suggestions';
			}
			
			return $finding;
		}
		
		return null;
	}
	
	/**
	 * Check if site is registered with WPShadow.
	 *
	 * @return bool True if site has registered (indicated by email consent).
	 */
	private static function is_site_registered() {
		$consent = get_option( 'wpshadow_email_consent', false );
		return ! empty( $consent );
	}
}
