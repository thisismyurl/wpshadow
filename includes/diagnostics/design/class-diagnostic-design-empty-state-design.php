<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Empty State Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-empty-state-design
 * Training: https://wpshadow.com/training/design-empty-state-design
 */
class Diagnostic_Design_EMPTY_STATE_DESIGN extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-empty-state-design',
            'title' => __('Empty State Design', 'wpshadow'),
            'description' => __('Checks empty states have illustration, explanation, and suggested next action.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-empty-state-design',
            'training_link' => 'https://wpshadow.com/training/design-empty-state-design',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
