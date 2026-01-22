<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS Duplication Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-duplication
 * Training: https://wpshadow.com/training/design-css-duplication
 */
class Diagnostic_Design_DESIGN_CSS_DUPLICATION {
    public static function check() {
        return [
            'id' => 'design-css-duplication',
            'title' => __('CSS Duplication Detection', 'wpshadow'),
            'description' => __('Detects duplicated rule blocks across stylesheets.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-duplication',
            'training_link' => 'https://wpshadow.com/training/design-css-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

