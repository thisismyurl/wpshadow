<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Media Query Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-media-query-bloat
 * Training: https://wpshadow.com/training/design-media-query-bloat
 */
class Diagnostic_Design_DESIGN_MEDIA_QUERY_BLOAT {
    public static function check() {
        return [
            'id' => 'design-media-query-bloat',
            'title' => __('Media Query Bloat', 'wpshadow'),
            'description' => __('Detects redundant or overlapping media queries.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-media-query-bloat',
            'training_link' => 'https://wpshadow.com/training/design-media-query-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

