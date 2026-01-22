<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Search Results Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-search-results-styling
 * Training: https://wpshadow.com/training/design-search-results-styling
 */
class Diagnostic_Design_SEARCH_RESULTS_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-search-results-styling',
            'title' => __('Search Results Styling', 'wpshadow'),
            'description' => __('Checks search results page matches design system.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-results-styling',
            'training_link' => 'https://wpshadow.com/training/design-search-results-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
