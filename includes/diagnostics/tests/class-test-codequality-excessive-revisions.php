<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Excessive Revisions (Code Quality)
 * 
 * Checks if post revisions are causing database bloat
 * Philosophy: Show value (#9) - reduces database size
 * 
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_CodeQuality_ExcessiveRevisions extends Diagnostic_Base {
    
    public static function check(): ?array {
        global $wpdb;
        
        $revision_count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
        );
        
        if ($revision_count > 500) {
            return [
                'id' => 'excessive-revisions',
                'title' => sprintf(__('%d post revisions stored', 'wpshadow'), $revision_count),
                'description' => __('Too many post revisions bloat the database. Set WP_POST_REVISIONS to limit revisions per post.', 'wpshadow'),
                'severity' => 'low',
                'threat_level' => 20,
            ];
        }
        
        return null;
    }
    
    public static function test_live_excessive_revisions(): array {
        $result = self::check();
        
        if (null === $result) {
            return [
                'passed' => true,
                'message' => __('Post revision count is reasonable', 'wpshadow'),
            ];
        }
        
        return [
            'passed' => false,
            'message' => $result['description'],
        ];
    }
}
