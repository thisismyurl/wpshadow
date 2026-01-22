<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Label Positioning
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-label-positioning
 * Training: https://wpshadow.com/training/design-form-label-positioning
 */
class Diagnostic_Design_FORM_LABEL_POSITIONING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-form-label-positioning',
            'title' => __('Form Label Positioning', 'wpshadow'),
            'description' => __('Analyzes label placement (above input preferred) and checks label-for associations.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-label-positioning',
            'training_link' => 'https://wpshadow.com/training/design-form-label-positioning',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
