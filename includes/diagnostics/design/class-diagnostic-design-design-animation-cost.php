<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Animation Cost
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-animation-cost
 * Training: https://wpshadow.com/training/design-animation-cost
 */
class Diagnostic_Design_DESIGN_ANIMATION_COST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-animation-cost',
            'title' => __('Animation Cost', 'wpshadow'),
            'description' => __('Detects long-running animations on large DOM segments.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-animation-cost',
            'training_link' => 'https://wpshadow.com/training/design-animation-cost',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}