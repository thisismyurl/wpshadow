<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: VRT Pattern Regressions
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-vrt-pattern-regressions
 * Training: https://wpshadow.com/training/design-vrt-pattern-regressions
 */
class Diagnostic_Design_DESIGN_VRT_PATTERN_REGRESSIONS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-vrt-pattern-regressions',
            'title' => __('VRT Pattern Regressions', 'wpshadow'),
            'description' => __('Captures baselines for registered patterns and block patterns.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-vrt-pattern-regressions',
            'training_link' => 'https://wpshadow.com/training/design-vrt-pattern-regressions',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
