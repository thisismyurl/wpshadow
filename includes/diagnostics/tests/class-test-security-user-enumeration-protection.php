<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: User Enumeration Protection (Security)
 * 
 * Checks if site is vulnerable to user enumeration attacks
 * Philosophy: Show value (#9) - prevents account discovery
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_UserEnumerationProtection extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if security plugin is protecting against user enumeration
        $plugins = get_plugins();
        $protection_active = false;
        
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (stripos($plugin_file, 'wordfence') !== false ||
                stripos($plugin_file, 'ithemes') !== false ||
                stripos($plugin_file, 'security') !== false) {
                if (is_plugin_active($plugin_file)) {
                    $protection_active = true;
                    break;
                }
            }
        }
        
        if (!$protection_active) {
            return [
                'id' => 'user-enumeration-protection',
                'title' => __('User enumeration not protected', 'wpshadow'),
                'description' => __('Attackers can discover user accounts via author URLs and REST API. Use a security plugin to disable user enumeration.', 'wpshadow'),
                'severity' => 'medium',
                'threat_level' => 45,
            ];
        }
        
        return null;
    }
    
    public static function test_live_user_enumeration_protection(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('User enumeration is protected', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
