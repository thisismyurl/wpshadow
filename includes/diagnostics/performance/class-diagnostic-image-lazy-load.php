<?php
declare(strict_types=1);
/**
 * Image Lazy Load Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if lazy loading for images is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Image_Lazy_Load extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_lazyload_enabled() ) {
			return array(
				'id'           => 'image-lazyload-disabled',
				'title'        => 'Image Lazy Loading Disabled',
				'description'  => 'Images are not using native lazy loading, which can slow down page loads.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-image-lazy-loading/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=lazyload',
				'auto_fixable' => true,
				'threat_level' => 40,
			);
		}
		
		return null;
	}
	
	private static function is_lazyload_enabled() {
		return apply_filters( 'wp_lazy_loading_enabled', true, 'the_content' ) || (bool) get_option( 'wpshadow_force_lazyload', false );
	}
}
