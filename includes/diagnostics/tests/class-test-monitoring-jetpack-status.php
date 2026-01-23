<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Jetpack Plugin Status (Monitoring)
 * 
 * Checks if Jetpack is installed and configured
 * Philosophy: Show value (#9) - Jetpack provides monitoring/backup
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_JetpackStatus extends Diagnostic_Base {
    
    public static function check(): ?array {
        $plugins = get_plugins();
        
        $jetpack_installed = false;
        $jetpack_active = false;
        
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (strpos($plugin_file, 'jetpack') === 0) {
                $jetpack_installed = true;
                if (is_plugin_active($plugin_file)) {
                    $jetpack_active = true;
                }
                break;
            }
        }
        
        // If Jetpack is not installed, it's optional so not an issue
        // If it's installed but not active, suggest activating
        if ($jetpack_installed && !$jetpack_active) {
            return [
                'id' => 'jetpack-status',
                'title' => __('Jetpack is installed but not active', 'wpshadow'),
                'description' => __('Activate Jetpack for automatic backups, security monitoring, and other features.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 15,
            ];
        }
        
        return null;
    }
    
    public static function test_live_jetpack_status(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Jetpack configuration is correct', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
