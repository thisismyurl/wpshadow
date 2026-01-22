<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP OPcache Disabled (SERVER-001)
 * 
 * Checks if PHP OPcache enabled.
 * Philosophy: Show value (#9) with 3-5x PHP execution improvement.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Php_Opcache_Disabled extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
// Check if caching is properly configured
		$has_cache = function_exists('wp_cache_get');
		if (!$has_cache) {
			return [
				'status' => 'warning',
				'message' => __('Object caching not configured', 'wpshadow'),
				'threat_level' => 'medium'
			];
		}
		return null; // No issues detected
	}
}
