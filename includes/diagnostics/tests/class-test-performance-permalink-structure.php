<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Permalink Structure Missing (Performance)
 * 
 * Checks if permalinks are properly configured
 * Philosophy: Show value (#9) - proper permalinks improve SEO/performance
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_PermalinkStructure extends Diagnostic_Base {
    
    public static function check(): ?array {
        $permalink_structure = get_option('permalink_structure');
        
        // Check if using default ugly permalinks (bad for SEO and performance)
        if (empty($permalink_structure)) {
            return [
                'id' => 'permalink-structure',
                'title' => __('Permalink structure is not optimized', 'wpshadow'),
                'description' => __('Enable pretty permalinks (Settings > Permalinks) to improve SEO and user experience.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 15,
            ];
        }
        
        return null;
    }
    
    public static function test_live_permalink_structure(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Permalink structure is properly optimized', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
