<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: State Transition Consistency
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-state-transition-consistency
 * Training: https://wpshadow.com/training/design-state-transition-consistency
 */
class Diagnostic_Design_DESIGN_STATE_TRANSITION_CONSISTENCY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-state-transition-consistency',
            'title' => __('State Transition Consistency', 'wpshadow'),
            'description' => __('Checks loading and disabled states are consistent.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-state-transition-consistency',
            'training_link' => 'https://wpshadow.com/training/design-state-transition-consistency',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}