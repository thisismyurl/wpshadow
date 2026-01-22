<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Analytics Tracking Active?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Analytics_Tracking extends Diagnostic_Base {
    protected static $slug = 'analytics-tracking';
    protected static $title = 'Analytics Tracking Active?';
    protected static $description = 'Verifies analytics code is firing correctly.';

    public static function check(): ?array {
        // Check for any analytics plugins
        $analytics_plugins = array(
            'google-analytics-for-wordpress/googleanalytics.php',
            'google-site-kit/google-site-kit.php',
            'ga-google-analytics/ga-google-analytics.php',
            'matomo/matomo.php',
        );
        
        foreach ($analytics_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - analytics plugin active
            }
        }
        
        // Check for analytics code patterns
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        $patterns = array('/UA-[0-9]+-[0-9]+/', '/G-[A-Z0-9]{10}/', '/GTM-[A-Z0-9]+/');
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $header_content)) {
                return null; // Pass - analytics code detected
            }
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No analytics tracking detected (GA, GTM, or Matomo).',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/analytics-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=analytics-tracking',
            'training_link' => 'https://wpshadow.com/training/analytics-tracking/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 1,
        );
    }
}
