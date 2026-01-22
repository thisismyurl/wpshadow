<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Form Field Full-Width Mobile
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-form-field-full-width-mobile
 * Training: https://wpshadow.com/training/design-form-field-full-width-mobile
 */
class Diagnostic_Design_FORM_FIELD_FULL_WIDTH_MOBILE {
    public static function check() {
        return [
            'id' => 'design-form-field-full-width-mobile',
            'title' => __('Form Field Full-Width Mobile', 'wpshadow'),
            'description' => __('Checks form inputs full-width on mobile.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-form-field-full-width-mobile',
            'training_link' => 'https://wpshadow.com/training/design-form-field-full-width-mobile',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
