<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Preload Misses
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-font-preload-miss
 * Training: https://wpshadow.com/training/design-font-preload-miss
 */
class Diagnostic_Design_DESIGN_FONT_PRELOAD_MISS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-font-preload-miss',
            'title' => __('Font Preload Misses', 'wpshadow'),
            'description' => __('Flags missing preload for critical fonts used above the fold.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-font-preload-miss',
            'training_link' => 'https://wpshadow.com/training/design-font-preload-miss',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}