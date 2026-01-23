<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: WordPress Core Integrity (Security)
 * 
 * Checks if WordPress core files have been modified
 * Philosophy: Show value (#9) - unmodified core prevents vulnerabilities
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_WordPressCoreIntegrity extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if a security scanner plugin is monitoring core files
        $plugins = get_plugins();
        $monitoring_active = false;
        
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (stripos($plugin_file, 'wordfence') !== false ||
                stripos($plugin_file, 'sucuri') !== false ||
                stripos($plugin_file, 'ithemes') !== false) {
                if (is_plugin_active($plugin_file)) {
                    $monitoring_active = true;
                    break;
                }
            }
        }
        
        if (!$monitoring_active) {
            return [
                'id' => 'wordpress-core-integrity',
                'title' => __('WordPress core files not being monitored', 'wpshadow'),
                'description' => __('Enable file integrity monitoring with a security plugin to detect unauthorized changes to WordPress core files.', 'wpshadow'),
                'severity' => 'medium',
                'threat_level' => 55,
            ];
        }
        
        return null;
    }
    
    public static function test_live_wordpress_core_integrity(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('WordPress core integrity is being monitored', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
