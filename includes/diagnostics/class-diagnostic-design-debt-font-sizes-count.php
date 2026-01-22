<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Font Size Variety
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-font-sizes-count
 * Training: https://wpshadow.com/training/design-debt-font-sizes-count
 */
class Diagnostic_Design_DEBT_FONT_SIZES_COUNT {
    public static function check() {
        return [
            'id' => 'design-debt-font-sizes-count',
            'title' => __('Font Size Variety', 'wpshadow'),
            'description' => __('Counts unique font sizes (should match system).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-font-sizes-count',
            'training_link' => 'https://wpshadow.com/training/design-debt-font-sizes-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
