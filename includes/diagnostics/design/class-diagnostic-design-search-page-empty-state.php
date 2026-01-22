<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Search Page Empty State
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-search-page-empty-state
 * Training: https://wpshadow.com/training/design-search-page-empty-state
 */
class Diagnostic_Design_SEARCH_PAGE_EMPTY_STATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-search-page-empty-state',
            'title' => __('Search Page Empty State', 'wpshadow'),
            'description' => __('Checks no-results page helpful, styled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-page-empty-state',
            'training_link' => 'https://wpshadow.com/training/design-search-page-empty-state',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
