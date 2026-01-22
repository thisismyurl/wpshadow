<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unused CSS Selectors
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-css-unused-selectors
 * Training: https://wpshadow.com/training/design-css-unused-selectors
 */
class Diagnostic_Design_DESIGN_CSS_UNUSED_SELECTORS {
    public static function check() {
        return [
            'id' => 'design-css-unused-selectors',
            'title' => __('Unused CSS Selectors', 'wpshadow'),
            'description' => __('Detects stylesheet selectors that never match the rendered DOM across templates.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-css-unused-selectors',
            'training_link' => 'https://wpshadow.com/training/design-css-unused-selectors',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

