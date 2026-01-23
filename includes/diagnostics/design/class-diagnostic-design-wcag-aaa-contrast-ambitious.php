<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG AAA Contrast Ambitious
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-wcag-aaa-contrast-ambitious
 * Training: https://wpshadow.com/training/design-wcag-aaa-contrast-ambitious
 */
class Diagnostic_Design_WCAG_AAA_CONTRAST_AMBITIOUS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-wcag-aaa-contrast-ambitious',
            'title' => __('WCAG AAA Contrast Ambitious', 'wpshadow'),
            'description' => __('Checks critical text aimed for 7:1 AAA ratio.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-wcag-aaa-contrast-ambitious',
            'training_link' => 'https://wpshadow.com/training/design-wcag-aaa-contrast-ambitious',
            'auto_fixable' => false,
            'threat_level' => 5
        ];
    }

}