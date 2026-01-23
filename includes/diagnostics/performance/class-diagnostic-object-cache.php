<?php
declare(strict_types=1);
/**
 * Object Cache Status Diagnostic
 *
 * Philosophy: Show value (#9) by highlighting performance gains from persistent object cache.
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check whether a persistent object cache is enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Object_Cache extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache() ) {
			return null; // Already optimized
		}
		
		return array(
			'id'          => 'object-cache',
			'title'       => 'Persistent Object Cache Not Enabled',
			'description' => 'A persistent object cache (Redis/Memcached) can significantly reduce database load and speed up your site.',
			'severity'    => 'medium',
			'category'    => 'performance',
			'kb_link'     => 'https://wpshadow.com/kb/enable-object-cache/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=object-cache',
			'auto_fixable' => false,
			'threat_level' => 55,
		);
	}

}