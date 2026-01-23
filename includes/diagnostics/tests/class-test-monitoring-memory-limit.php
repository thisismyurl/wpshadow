<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Memory Limit Configuration (Monitoring)
 * 
 * Checks if WordPress memory limit is properly configured
 * Philosophy: Show value (#9) - adequate memory prevents crashes
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_MemoryLimit extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Get WordPress memory limit in bytes
        $memory_limit = WP_MEMORY_LIMIT;
        
        if (function_exists('wp_convert_hr_to_bytes')) {
            $limit_bytes = wp_convert_hr_to_bytes($memory_limit);
        } else {
            // Fallback conversion
            $limit_bytes = intval($memory_limit);
        }
        
        // 40MB is the minimum recommended
        $min_bytes = 40 * 1024 * 1024;
        
        if ($limit_bytes < $min_bytes) {
            return [
                'id' => 'memory-limit',
                'title' => sprintf(__('WordPress memory limit is too low (%s)', 'wpshadow'), $memory_limit),
                'description' => __('Increase WP_MEMORY_LIMIT in wp-config.php to at least 256MB to prevent crashes.', 'wpshadow'),
                'severity' => 'medium',
                'threat_level' => 50,
            ];
        }
        
        return null;
    }
    
    public static function test_live_memory_limit(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => sprintf(__('Memory limit is properly configured (%s)', 'wpshadow'), WP_MEMORY_LIMIT),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
