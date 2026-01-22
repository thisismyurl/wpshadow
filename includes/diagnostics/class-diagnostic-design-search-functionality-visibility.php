<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Search Functionality Visibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-search-functionality-visibility
 * Training: https://wpshadow.com/training/design-search-functionality-visibility
 */
class Diagnostic_Design_SEARCH_FUNCTIONALITY_VISIBILITY {
    public static function check() {
        return [
            'id' => 'design-search-functionality-visibility',
            'title' => __('Search Functionality Visibility', 'wpshadow'),
            'description' => __('Confirms search box prominent in header.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-functionality-visibility',
            'training_link' => 'https://wpshadow.com/training/design-search-functionality-visibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
