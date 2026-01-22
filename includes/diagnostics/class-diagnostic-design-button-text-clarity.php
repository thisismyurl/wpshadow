<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Button Text Clarity
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-button-text-clarity
 * Training: https://wpshadow.com/training/design-button-text-clarity
 */
class Diagnostic_Design_BUTTON_TEXT_CLARITY {
    public static function check() {
        return [
            'id' => 'design-button-text-clarity',
            'title' => __('Button Text Clarity', 'wpshadow'),
            'description' => __('Validates button text descriptive ('Submit' not 'OK').', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-button-text-clarity',
            'training_link' => 'https://wpshadow.com/training/design-button-text-clarity',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
