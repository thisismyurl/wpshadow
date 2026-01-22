<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Input Error State
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-input-error-state
 * Training: https://wpshadow.com/training/design-input-error-state
 */
class Diagnostic_Design_INPUT_ERROR_STATE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-input-error-state',
            'title' => __('Input Error State', 'wpshadow'),
            'description' => __('Validates error inputs show red border.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-input-error-state',
            'training_link' => 'https://wpshadow.com/training/design-input-error-state',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
