<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: CSS-in-JS Runtime Size
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-css-in-js-runtime
 * Training: https://wpshadow.com/training/design-debt-css-in-js-runtime
 */
class Diagnostic_Design_DEBT_CSS_IN_JS_RUNTIME {
    public static function check() {
        return [
            'id' => 'design-debt-css-in-js-runtime',
            'title' => __('CSS-in-JS Runtime Size', 'wpshadow'),
            'description' => __('Measures CSS-in-JS runtime bundle size impact.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-css-in-js-runtime',
            'training_link' => 'https://wpshadow.com/training/design-debt-css-in-js-runtime',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
