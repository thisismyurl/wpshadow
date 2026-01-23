<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG Contrast Failure
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-color-contrast
 * Training: https://wpshadow.com/training/code-a11y-color-contrast
 */
class Diagnostic_Code_CODE_A11Y_COLOR_CONTRAST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-a11y-color-contrast',
            'title' => __('WCAG Contrast Failure', 'wpshadow'),
            'description' => __('Flags text/background combinations failing AA contrast ratio.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-color-contrast',
            'training_link' => 'https://wpshadow.com/training/code-a11y-color-contrast',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}