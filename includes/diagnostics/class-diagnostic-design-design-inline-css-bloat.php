<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Inline CSS Bloat
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-inline-css-bloat
 * Training: https://wpshadow.com/training/design-inline-css-bloat
 */
class Diagnostic_Design_DESIGN_INLINE_CSS_BLOAT {
    public static function check() {
        return [
            'id' => 'design-inline-css-bloat',
            'title' => __('Inline CSS Bloat', 'wpshadow'),
            'description' => __('Detects excessive inline CSS per page.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-inline-css-bloat',
            'training_link' => 'https://wpshadow.com/training/design-inline-css-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

