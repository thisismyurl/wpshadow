<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Unused Themes Consuming Resources (Code Quality)
 * 
 * Checks for inactive themes that should be deleted
 * Philosophy: Show value (#9) - keeps codebase clean
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_CodeQuality_UnusedThemes extends Diagnostic_Base {
    
    public static function check(): ?array {
        $themes = wp_get_themes();
        $current_theme = wp_get_theme()->get('Template');
        $parent_theme = wp_get_theme()->get_template();
        
        $inactive_count = 0;
        foreach ($themes as $theme) {
            $template = $theme->get('Template');
            if ($template !== $current_theme && $template !== $parent_theme) {
                $inactive_count++;
            }
        }
        
        if ($inactive_count > 3) {
            return [
                'id' => 'unused-themes',
                'title' => sprintf(__('%d inactive themes found', 'wpshadow'), $inactive_count),
                'description' => __('Delete unused themes to reduce attack surface and free up disk space.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 25,
            ];
        }
        
        return null;
    }
    
    public static function test_live_unused_themes(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('No excessive inactive themes found', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
