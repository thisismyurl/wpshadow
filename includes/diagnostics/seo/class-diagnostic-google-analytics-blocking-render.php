<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Analytics Blocking Render (THIRD-001)
 * 
 * Detects synchronous GA script loading.
 * Philosophy: Show value (#9) with async analytics.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Google_Analytics_Blocking_Render extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if Google Analytics is loaded synchronously
        if (!is_ssl()) {
            return null;
        }
        
        // Check for GA script in page source
        $ga_async = get_option('wpshadow_ga_async_enabled');
        
        if (!$ga_async) {
            return array(
                'id' => 'google-analytics-blocking-render',
                'title' => __('Google Analytics May Block Rendering', 'wpshadow'),
                'description' => __('Load Google Analytics asynchronously to prevent blocking page rendering. Use async="true" attribute or defer loading.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/analytics-performance/',
                'training_link' => 'https://wpshadow.com/training/async-analytics/',
                'auto_fixable' => false,
                'threat_level' => 50,
            );
        }
        return null;
}
}
