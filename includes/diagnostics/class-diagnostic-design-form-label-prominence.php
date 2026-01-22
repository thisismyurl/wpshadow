<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Form Label Prominence
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-label-prominence
 * Training: https://wpshadow.com/training/design-form-label-prominence
 */
class Diagnostic_Design_FORM_LABEL_PROMINENCE {
    public static function check() {
        return [
            'id' => 'design-form-label-prominence',
            'title' => __('Form Label Prominence', 'wpshadow'),
            'description' => __('Validates form labels prominent enough.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-label-prominence',
            'training_link' => 'https://wpshadow.com/training/design-form-label-prominence',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
