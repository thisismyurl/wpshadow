<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Button Loading State
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-loading-state
 * Training: https://wpshadow.com/training/design-button-loading-state
 */
class Diagnostic_Design_BUTTON_LOADING_STATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-button-loading-state',
            'title' => __('Button Loading State', 'wpshadow'),
            'description' => __('Checks buttons show loading spinner.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-loading-state',
            'training_link' => 'https://wpshadow.com/training/design-button-loading-state',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}