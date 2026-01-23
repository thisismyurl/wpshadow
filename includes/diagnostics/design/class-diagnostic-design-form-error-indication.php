<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Error Indication
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-error-indication
 * Training: https://wpshadow.com/training/design-form-error-indication
 */
class Diagnostic_Design_FORM_ERROR_INDICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-error-indication',
            'title' => __('Form Error Indication', 'wpshadow'),
            'description' => __('Confirms errors indicated by color + icon + text.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-error-indication',
            'training_link' => 'https://wpshadow.com/training/design-form-error-indication',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}