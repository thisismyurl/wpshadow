<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Component Consistency Score
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-component-consistency-score
 * Training: https://wpshadow.com/training/design-component-consistency-score
 */
class Diagnostic_Design_DESIGN_COMPONENT_CONSISTENCY_SCORE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-component-consistency-score',
            'title' => __('Component Consistency Score', 'wpshadow'),
            'description' => __('Scores component spacing and typography consistency per template.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-component-consistency-score',
            'training_link' => 'https://wpshadow.com/training/design-component-consistency-score',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}