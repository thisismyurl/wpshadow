<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Active State
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-active-state
 * Training: https://wpshadow.com/training/design-button-active-state
 */
class Diagnostic_Design_BUTTON_ACTIVE_STATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-active-state',
            'title' => __('Button Active State', 'wpshadow'),
            'description' => __('Confirms button active state distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-active-state',
            'training_link' => 'https://wpshadow.com/training/design-button-active-state',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
