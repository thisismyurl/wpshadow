<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Sorting Options Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-sorting-options-clarity
 * Training: https://wpshadow.com/training/design-sorting-options-clarity
 */
class Diagnostic_Design_SORTING_OPTIONS_CLARITY {
    public static function check() {
        return [
            'id' => 'design-sorting-options-clarity',
            'title' => __('Sorting Options Clarity', 'wpshadow'),
            'description' => __('Confirms sorting options clearly labeled.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-sorting-options-clarity',
            'training_link' => 'https://wpshadow.com/training/design-sorting-options-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
