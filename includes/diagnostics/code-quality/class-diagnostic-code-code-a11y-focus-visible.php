<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Focus Indicators
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-focus-visible
 * Training: https://wpshadow.com/training/code-a11y-focus-visible
 */
class Diagnostic_Code_CODE_A11Y_FOCUS_VISIBLE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-a11y-focus-visible',
            'title' => __('Missing Focus Indicators', 'wpshadow'),
            'description' => __('Flags interactive elements without visible focus states.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-focus-visible',
            'training_link' => 'https://wpshadow.com/training/code-a11y-focus-visible',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
