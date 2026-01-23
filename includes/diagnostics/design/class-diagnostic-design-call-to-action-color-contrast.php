<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CTA Color Contrast
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-call-to-action-color-contrast
 * Training: https://wpshadow.com/training/design-call-to-action-color-contrast
 */
class Diagnostic_Design_CALL_TO_ACTION_COLOR_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-call-to-action-color-contrast',
            'title' => __('CTA Color Contrast', 'wpshadow'),
            'description' => __('Confirms CTA button contrast meets WCAG standards.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-call-to-action-color-contrast',
            'training_link' => 'https://wpshadow.com/training/design-call-to-action-color-contrast',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}