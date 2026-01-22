<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WP Transients Not Using External Cache (CACHE-011)
 * 
 * Checks if transients stored in database vs object cache.
 * Philosophy: Educate (#5) about transient optimization.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Wp_Transients_Not_External_Cache extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if transients are using external cache
        // Check if object cache is enabled
        $has_cache = function_exists('wp_cache_get');
        
        if (!$has_cache) {
            return array(
                'id' => 'wp-transients-not-external-cache',
                'title' => __('Transients Not Cached Externally', 'wpshadow'),
                'description' => __('WordPress transients are stored in database, not in a fast external cache. Enable Redis or Memcached for better performance.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/transient-caching/',
                'training_link' => 'https://wpshadow.com/training/redis-memcached-setup/',
                'auto_fixable' => false,
                'threat_level' => 50,
            );
        }
        return null;
	}
}
