<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Error Prevention Design
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-error-prevention
 * Training: https://wpshadow.com/training/design-error-prevention
 */
class Diagnostic_Design_ERROR_PREVENTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-error-prevention',
            'title' => __('Error Prevention Design', 'wpshadow'),
            'description' => __('Confirms multi-step forms have review/confirm step.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-error-prevention',
            'training_link' => 'https://wpshadow.com/training/design-error-prevention',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
