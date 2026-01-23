<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Fallback Coverage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-font-fallback-coverage
 * Training: https://wpshadow.com/training/design-font-fallback-coverage
 */
class Diagnostic_Design_DESIGN_FONT_FALLBACK_COVERAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-fallback-coverage',
            'title' => __('Font Fallback Coverage', 'wpshadow'),
            'description' => __('Verifies glyph coverage and safe fallbacks per locale.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-fallback-coverage',
            'training_link' => 'https://wpshadow.com/training/design-font-fallback-coverage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}