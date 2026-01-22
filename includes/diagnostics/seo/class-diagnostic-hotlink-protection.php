<?php
declare(strict_types=1);
/**
 * Hotlink Protection Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if basic hotlink protection is enabled for media assets.
 */
class Diagnostic_Hotlink_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_apache_like() ) {
			return null; // Only evaluate on Apache-like setups.
		}
		
		if ( ! self::has_hotlink_rules() ) {
			return array(
				'id'           => 'hotlink-protection-missing',
				'title'        => 'Hotlink Protection Not Enabled',
				'description'  => 'Blocking image hotlinking saves bandwidth and prevents unauthorized re-use of your media.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-hotlink-protection/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=hotlink-protection',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
	
	/**
	 * Determine if server is Apache-like and supports .htaccess rules.
	 *
	 * @return bool
	 */
	private static function is_apache_like() {
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			return in_array( 'mod_rewrite', $modules, true );
		}
		
		return ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( $_SERVER['SERVER_SOFTWARE'], 'apache' ) );
	}
	
	/**
	 * Check if the WPShadow hotlink protection block exists in .htaccess.
	 *
	 * @return bool
	 */
	private static function has_hotlink_rules() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			return false;
		}
		
		$contents = file_get_contents( $htaccess );
		return false !== strpos( $contents, '# BEGIN WPShadow Hotlink Protection' );
	}
}
