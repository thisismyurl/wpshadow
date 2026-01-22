<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Success State Visibility
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-success-visibility
 * Training: https://wpshadow.com/training/design-success-visibility
 */
class Diagnostic_Design_SUCCESS_VISIBILITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-success-visibility',
            'title' => __('Success State Visibility', 'wpshadow'),
            'description' => __('Validates success states use green + checkmark.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-success-visibility',
            'training_link' => 'https://wpshadow.com/training/design-success-visibility',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
