<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Search Input Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-search-input-design
 * Training: https://wpshadow.com/training/design-search-input-design
 */
class Diagnostic_Design_SEARCH_INPUT_DESIGN {
    public static function check() {
        return [
            'id' => 'design-search-input-design',
            'title' => __('Search Input Design', 'wpshadow'),
            'description' => __('Confirms search inputs show icon.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-search-input-design',
            'training_link' => 'https://wpshadow.com/training/design-search-input-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
