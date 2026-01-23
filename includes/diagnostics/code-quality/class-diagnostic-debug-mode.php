<?php
declare(strict_types=1);
/**
 * Debug Mode Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if debug mode is enabled on live site.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Debug_Mode extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'           => 'debug-mode-enabled',
				'title'        => 'Debug Mode Enabled',
				'description'  => 'WordPress debug mode is active. Disable it on live sites for better security.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/disable-wordpress-debug-mode/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=debug-mode',
				'auto_fixable' => self::can_modify_wp_config(),
				'threat_level' => 70,
			);
		}
		
		return null;
	}
	
	/**
	 * Check if we can modify wp-config.php.
	 *
	 * @return bool True if wp-config.php is writable.
	 */
	private static function can_modify_wp_config() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return file_exists( $config_file ) && is_writable( $config_file );
	}

}