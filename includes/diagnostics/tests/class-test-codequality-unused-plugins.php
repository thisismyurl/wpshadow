<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Unused Plugins Consuming Resources (Code Quality)
 * 
 * Checks for inactive plugins that should be deleted
 * Philosophy: Show value (#9) - removes bloat, improves performance
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_CodeQuality_UnusedPlugins extends Diagnostic_Base {
    
    public static function check(): ?array {
        $plugins = get_plugins();
        $active = get_option('active_plugins', []);
        
        $inactive_count = 0;
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (!in_array($plugin_file, $active, true)) {
                $inactive_count++;
            }
        }
        
        if ($inactive_count > 5) {
            return [
                'id' => 'unused-plugins',
                'title' => sprintf(__('%d inactive plugins found', 'wpshadow'), $inactive_count),
                'description' => __('Delete inactive plugins to reduce attack surface and free up disk space.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 30,
            ];
        }
        
        return null;
    }
    
    public static function test_live_unused_plugins(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('No excessive inactive plugins found', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
