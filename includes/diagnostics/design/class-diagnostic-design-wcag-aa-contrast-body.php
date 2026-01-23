<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG AA Contrast Body Text
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-wcag-aa-contrast-body
 * Training: https://wpshadow.com/training/design-wcag-aa-contrast-body
 */
class Diagnostic_Design_WCAG_AA_CONTRAST_BODY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-wcag-aa-contrast-body',
            'title' => __('WCAG AA Contrast Body Text', 'wpshadow'),
            'description' => __('Confirms body text/background meets 4.5:1 ratio.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-wcag-aa-contrast-body',
            'training_link' => 'https://wpshadow.com/training/design-wcag-aa-contrast-body',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}