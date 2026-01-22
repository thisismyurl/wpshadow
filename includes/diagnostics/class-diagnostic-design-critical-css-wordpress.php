<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Critical CSS Inlining
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-critical-css-wordpress
 * Training: https://wpshadow.com/training/design-critical-css-wordpress
 */
class Diagnostic_Design_CRITICAL_CSS_WORDPRESS {
    public static function check() {
        return [
            'id' => 'design-critical-css-wordpress',
            'title' => __('Critical CSS Inlining', 'wpshadow'),
            'description' => __('Checks critical CSS inlined via wp_head().', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-critical-css-wordpress',
            'training_link' => 'https://wpshadow.com/training/design-critical-css-wordpress',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
