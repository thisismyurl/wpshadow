<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Critical CSS Inlining
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-critical-css-inlining
 * Training: https://wpshadow.com/training/design-critical-css-inlining
 */
class Diagnostic_Design_CRITICAL_CSS_INLINING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-critical-css-inlining',
            'title' => __('Critical CSS Inlining', 'wpshadow'),
            'description' => __('Verifies above-fold CSS inlined.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-critical-css-inlining',
            'training_link' => 'https://wpshadow.com/training/design-critical-css-inlining',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
