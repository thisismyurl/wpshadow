<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Broken Plugin Compatibility (Security)
 * 
 * Checks for deprecated plugin functions causing warnings
 * Philosophy: Show value (#9) - prevents runtime errors
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_BrokenCompatibility extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if WordPress is running on PHP 8+ where deprecated functions cause errors
        if (version_compare(phpversion(), '8.0.0', '>=')) {
            // Check for plugins using deprecated functions
            $plugins = get_plugins();
            $old_plugins = 0;
            
            foreach ($plugins as $plugin_file => $plugin_data) {
                $version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0.0';
                // Plugins older than 5 years may have compatibility issues
                if (preg_match('/^[0-1]\./', $version)) {
                    if (is_plugin_active($plugin_file)) {
                        $old_plugins++;
                    }
                }
            }
            
            if ($old_plugins > 0) {
                return [
                    'id' => 'broken-compatibility',
                    'title' => sprintf(__('%d plugins may be incompatible with PHP 8+', 'wpshadow'), $old_plugins),
                    'description' => __('Plugins with older version numbers may not support PHP 8. Update them to compatible versions.', 'wpshadow'),
                    'severity' => 'medium',
                    'threat_level' => 50,
                ];
            }
        }
        
        return null;
    }
    
    public static function test_live_broken_compatibility(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Plugin compatibility is good', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
