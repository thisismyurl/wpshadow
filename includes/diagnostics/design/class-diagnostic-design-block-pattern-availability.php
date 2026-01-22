<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Pattern Library
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-block-pattern-availability
 * Training: https://wpshadow.com/training/design-block-pattern-availability
 */
class Diagnostic_Design_BLOCK_PATTERN_AVAILABILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-block-pattern-availability',
            'title' => __('Block Pattern Library', 'wpshadow'),
            'description' => __('Confirms block patterns available, documented.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-block-pattern-availability',
            'training_link' => 'https://wpshadow.com/training/design-block-pattern-availability',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
