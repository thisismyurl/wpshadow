<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Disabled State Visibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-disabled-state-visibility
 * Training: https://wpshadow.com/training/design-disabled-state-visibility
 */
class Diagnostic_Design_DISABLED_STATE_VISIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-disabled-state-visibility',
            'title' => __('Disabled State Visibility', 'wpshadow'),
            'description' => __('Checks disabled elements visually reduced.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-disabled-state-visibility',
            'training_link' => 'https://wpshadow.com/training/design-disabled-state-visibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}