<?php
declare(strict_types=1);
/**
 * External Fonts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Detect usage of Google-hosted fonts that could be inlined or removed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_External_Fonts extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$external_handles = self::detect_external_font_handles();
		$blocked          = (bool) get_option( 'wpshadow_block_external_fonts', false );
		
		if ( empty( $external_handles ) || $blocked ) {
			return null;
		}
		
		$list = implode( ', ', $external_handles );
		
		return array(
			'id'           => 'external-fonts-loading',
			'title'        => 'External Fonts Loaded (Google)',
			'description'  => 'These styles load Google Fonts: ' . $list . '. Consider switching to a system stack to improve privacy and performance.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/remove-google-fonts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=external-fonts',
			'auto_fixable' => true,
			'threat_level' => 30,
		);
	}
	
	private static function detect_external_font_handles() {
		global $wp_styles;
		if ( ! isset( $wp_styles ) || empty( $wp_styles->queue ) ) {
			return array();
		}
		
		$handles = array();
		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
				continue;
			}
			$src = $wp_styles->registered[ $handle ]->src;
			if ( is_string( $src ) && self::is_google_font_src( $src ) ) {
				$handles[] = $handle;
			}
		}
		
		return $handles;
	}
	
	private static function is_google_font_src( $src ) {
		return ( false !== stripos( $src, 'fonts.googleapis.com' ) || false !== stripos( $src, 'fonts.gstatic.com' ) );
	}

}