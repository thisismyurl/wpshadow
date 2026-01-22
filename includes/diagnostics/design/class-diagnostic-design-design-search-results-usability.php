<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Search Results Usability
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-search-results-usability
 * Training: https://wpshadow.com/training/design-search-results-usability
 */
class Diagnostic_Design_DESIGN_SEARCH_RESULTS_USABILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-search-results-usability',
            'title' => __('Search Results Usability', 'wpshadow'),
            'description' => __('Checks search results layouts and pagination usability.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-results-usability',
            'training_link' => 'https://wpshadow.com/training/design-search-results-usability',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
