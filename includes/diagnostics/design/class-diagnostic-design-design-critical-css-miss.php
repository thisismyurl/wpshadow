<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Critical CSS Misses
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-critical-css-miss
 * Training: https://wpshadow.com/training/design-critical-css-miss
 */
class Diagnostic_Design_DESIGN_CRITICAL_CSS_MISS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-critical-css-miss',
            'title' => __('Critical CSS Misses', 'wpshadow'),
            'description' => __('Flags missing critical CSS for largest contentful paint elements.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-critical-css-miss',
            'training_link' => 'https://wpshadow.com/training/design-critical-css-miss',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}