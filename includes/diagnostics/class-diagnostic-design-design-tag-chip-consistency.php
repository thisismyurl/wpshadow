<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Tag and Chip Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-tag-chip-consistency
 * Training: https://wpshadow.com/training/design-tag-chip-consistency
 */
class Diagnostic_Design_DESIGN_TAG_CHIP_CONSISTENCY {
    public static function check() {
        return [
            'id' => 'design-tag-chip-consistency',
            'title' => __('Tag and Chip Consistency', 'wpshadow'),
            'description' => __('Checks badge and pill radius, padding, and typography consistency.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-tag-chip-consistency',
            'training_link' => 'https://wpshadow.com/training/design-tag-chip-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

