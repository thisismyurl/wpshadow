<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mobile Number Input Spinner
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-mobile-number-input-spinner
 * Training: https://wpshadow.com/training/design-mobile-number-input-spinner
 */
class Diagnostic_Design_MOBILE_NUMBER_INPUT_SPINNER {
    public static function check() {
        return [
            'id' => 'design-mobile-number-input-spinner',
            'title' => __('Mobile Number Input Spinner', 'wpshadow'),
            'description' => __('Checks number inputs show spinner.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-mobile-number-input-spinner',
            'training_link' => 'https://wpshadow.com/training/design-mobile-number-input-spinner',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
