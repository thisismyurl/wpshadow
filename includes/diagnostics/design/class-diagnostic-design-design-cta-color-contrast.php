<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CTA Color Contrast
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-cta-color-contrast
 * Training: https://wpshadow.com/training/design-cta-color-contrast
 */
class Diagnostic_Design_DESIGN_CTA_COLOR_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-cta-color-contrast',
            'title' => __('CTA Color Contrast', 'wpshadow'),
            'description' => __('Checks CTA contrast remains safe when colors change.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-cta-color-contrast',
            'training_link' => 'https://wpshadow.com/training/design-cta-color-contrast',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}