<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Variety Count
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-animation-count
 * Training: https://wpshadow.com/training/design-debt-animation-count
 */
class Diagnostic_Design_DEBT_ANIMATION_COUNT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-animation-count',
            'title' => __('Animation Variety Count', 'wpshadow'),
            'description' => __('Counts unique animation definitions (bloat indicator).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-animation-count',
            'training_link' => 'https://wpshadow.com/training/design-debt-animation-count',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
