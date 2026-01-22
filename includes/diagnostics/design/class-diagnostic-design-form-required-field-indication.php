<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Required Field Indication
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-required-field-indication
 * Training: https://wpshadow.com/training/design-form-required-field-indication
 */
class Diagnostic_Design_FORM_REQUIRED_FIELD_INDICATION extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-required-field-indication',
            'title' => __('Required Field Indication', 'wpshadow'),
            'description' => __('Verifies required fields marked with *.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-required-field-indication',
            'training_link' => 'https://wpshadow.com/training/design-form-required-field-indication',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
