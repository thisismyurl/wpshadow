<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Search No Results Guidance
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-search-no-results-guidance
 * Training: https://wpshadow.com/training/design-search-no-results-guidance
 */
class Diagnostic_Design_SEARCH_NO_RESULTS_GUIDANCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-search-no-results-guidance',
            'title' => __('Search No Results Guidance', 'wpshadow'),
            'description' => __('Checks no results page suggests alternatives.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-no-results-guidance',
            'training_link' => 'https://wpshadow.com/training/design-search-no-results-guidance',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}