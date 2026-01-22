<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Block Pattern Library
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-pattern-library
 * Training: https://wpshadow.com/training/design-block-pattern-library
 */
class Diagnostic_Design_DESIGN_BLOCK_PATTERN_LIBRARY {
    public static function check() {
        return [
            'id' => 'design-block-pattern-library',
            'title' => __('Block Pattern Library', 'wpshadow'),
            'description' => __('Checks patterns are registered, used, and not duplicated.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-pattern-library',
            'training_link' => 'https://wpshadow.com/training/design-block-pattern-library',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

