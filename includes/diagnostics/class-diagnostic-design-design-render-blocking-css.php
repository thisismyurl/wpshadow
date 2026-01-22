<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Render-Blocking CSS
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-render-blocking-css
 * Training: https://wpshadow.com/training/design-render-blocking-css
 */
class Diagnostic_Design_DESIGN_RENDER_BLOCKING_CSS {
    public static function check() {
        return [
            'id' => 'design-render-blocking-css',
            'title' => __('Render-Blocking CSS', 'wpshadow'),
            'description' => __('Detects large critical CSS and missing preload or defer for non-critical CSS.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-render-blocking-css',
            'training_link' => 'https://wpshadow.com/training/design-render-blocking-css',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

