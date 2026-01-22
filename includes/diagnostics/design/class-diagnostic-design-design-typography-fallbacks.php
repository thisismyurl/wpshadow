<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Typography Fallbacks
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-typography-fallbacks
 * Training: https://wpshadow.com/training/design-typography-fallbacks
 */
class Diagnostic_Design_DESIGN_TYPOGRAPHY_FALLBACKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-typography-fallbacks',
            'title' => __('Typography Fallbacks', 'wpshadow'),
            'description' => __('Checks safe fallbacks when fonts are unavailable.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-typography-fallbacks',
            'training_link' => 'https://wpshadow.com/training/design-typography-fallbacks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
