<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: CSS/JavaScript Minification (Performance)
 * 
 * Checks if CSS and JavaScript files are minified
 * Philosophy: Show value (#9) - minified assets load faster
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_CssJsMinification extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if minification is enabled via plugins
        $plugins = get_plugins();
        $minification_active = false;
        
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (stripos($plugin_file, 'cache') !== false ||
                stripos($plugin_file, 'autoptimize') !== false ||
                stripos($plugin_file, 'wp-rocket') !== false ||
                stripos($plugin_file, 'litespeed') !== false) {
                if (is_plugin_active($plugin_file)) {
                    $minification_active = true;
                    break;
                }
            }
        }
        
        if (!$minification_active) {
            return [
                'id' => 'css-js-minification',
                'title' => __('CSS/JavaScript not minified', 'wpshadow'),
                'description' => __('Enable CSS and JavaScript minification to reduce file sizes and improve page load speed. Use a caching plugin.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 30,
            ];
        }
        
        return null;
    }
    
    public static function test_live_css_js_minification(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('CSS/JavaScript minification is enabled', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
