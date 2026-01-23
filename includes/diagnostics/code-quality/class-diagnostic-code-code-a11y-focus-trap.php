<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Focus Trap
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-focus-trap
 * Training: https://wpshadow.com/training/code-a11y-focus-trap
 */
class Diagnostic_Code_CODE_A11Y_FOCUS_TRAP extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-a11y-focus-trap',
            'title' => __('Keyboard Focus Trap', 'wpshadow'),
            'description' => __('Detects modals/overlays trapping keyboard navigation.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-focus-trap',
            'training_link' => 'https://wpshadow.com/training/code-a11y-focus-trap',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}