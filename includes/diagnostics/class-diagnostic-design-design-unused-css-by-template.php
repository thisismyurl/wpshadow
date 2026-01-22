<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unused CSS By Template
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-unused-css-by-template
 * Training: https://wpshadow.com/training/design-unused-css-by-template
 */
class Diagnostic_Design_DESIGN_UNUSED_CSS_BY_TEMPLATE {
    public static function check() {
        return [
            'id' => 'design-unused-css-by-template',
            'title' => __('Unused CSS By Template', 'wpshadow'),
            'description' => __('Estimates unused CSS per template and flags high dead weight.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-unused-css-by-template',
            'training_link' => 'https://wpshadow.com/training/design-unused-css-by-template',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

