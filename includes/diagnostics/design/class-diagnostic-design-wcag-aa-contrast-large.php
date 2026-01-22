<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG AA Contrast Large Text
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-wcag-aa-contrast-large
 * Training: https://wpshadow.com/training/design-wcag-aa-contrast-large
 */
class Diagnostic_Design_WCAG_AA_CONTRAST_LARGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-wcag-aa-contrast-large',
            'title' => __('WCAG AA Contrast Large Text', 'wpshadow'),
            'description' => __('Validates large text meets 3:1 ratio minimum.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-wcag-aa-contrast-large',
            'training_link' => 'https://wpshadow.com/training/design-wcag-aa-contrast-large',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }
}
