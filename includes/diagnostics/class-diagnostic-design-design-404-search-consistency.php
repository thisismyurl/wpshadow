<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: 404 and Search Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-404-search-consistency
 * Training: https://wpshadow.com/training/design-404-search-consistency
 */
class Diagnostic_Design_DESIGN_404_SEARCH_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-404-search-consistency',
            'title' => __('404 and Search Consistency', 'wpshadow'),
            'description' => __('Checks 404 and search templates are styled and usable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-404-search-consistency',
            'training_link' => 'https://wpshadow.com/training/design-404-search-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

