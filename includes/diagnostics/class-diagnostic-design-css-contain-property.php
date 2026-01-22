<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS Contain Property Usage
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-css-contain-property
 * Training: https://wpshadow.com/training/design-css-contain-property
 */
class Diagnostic_Design_CSS_CONTAIN_PROPERTY {
    public static function check() {
        return [
            'id' => 'design-css-contain-property',
            'title' => __('CSS Contain Property Usage', 'wpshadow'),
            'description' => __('Confirms CSS contain used.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-contain-property',
            'training_link' => 'https://wpshadow.com/training/design-css-contain-property',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
