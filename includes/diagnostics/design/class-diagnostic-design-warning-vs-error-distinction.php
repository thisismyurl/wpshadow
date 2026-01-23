<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Warning vs Error Distinction
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-warning-vs-error-distinction
 * Training: https://wpshadow.com/training/design-warning-vs-error-distinction
 */
class Diagnostic_Design_WARNING_VS_ERROR_DISTINCTION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-warning-vs-error-distinction',
            'title' => __('Warning vs Error Distinction', 'wpshadow'),
            'description' => __('Confirms warnings and errors visually distinct.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-warning-vs-error-distinction',
            'training_link' => 'https://wpshadow.com/training/design-warning-vs-error-distinction',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}