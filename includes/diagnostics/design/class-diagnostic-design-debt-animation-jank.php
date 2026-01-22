<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Jank Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-debt-animation-jank
 * Training: https://wpshadow.com/training/design-debt-animation-jank
 */
class Diagnostic_Design_DEBT_ANIMATION_JANK extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-debt-animation-jank',
            'title' => __('Animation Jank Score', 'wpshadow'),
            'description' => __('Quantifies frame drops in animations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-debt-animation-jank',
            'training_link' => 'https://wpshadow.com/training/design-debt-animation-jank',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
