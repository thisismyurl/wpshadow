<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Search Engine Visibility (Performance)
 * 
 * Checks if site is visible to search engines
 * Philosophy: Show value (#9) - visibility drives traffic
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_SearchEngineVisibility extends Diagnostic_Base {
    
    public static function check(): ?array {
        // Check if blog is public
        $public = get_option('blog_public');
        
        if (empty($public) || $public === '0') {
            return [
                'id' => 'search-engine-visibility',
                'title' => __('Site is hidden from search engines', 'wpshadow'),
                'description' => __('Enable search engine visibility (Settings > Reading) to allow search engines to index your content.', 'wpshadow'),
                'severity' => 'medium',
                'threat_level' => 50,
            ];
        }
        
        return null;
    }
    
    public static function test_live_search_engine_visibility(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Site is visible to search engines', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
