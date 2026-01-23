<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Search Result Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-search-result-clarity
 * Training: https://wpshadow.com/training/design-search-result-clarity
 */
class Diagnostic_Design_SEARCH_RESULT_CLARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-search-result-clarity',
            'title' => __('Search Result Clarity', 'wpshadow'),
            'description' => __('Validates search results show title, excerpt, date.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-result-clarity',
            'training_link' => 'https://wpshadow.com/training/design-search-result-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}