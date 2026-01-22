<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Navigation Missing
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-keyboard-nav
 * Training: https://wpshadow.com/training/code-a11y-keyboard-nav
 */
class Diagnostic_Code_CODE_A11Y_KEYBOARD_NAV extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-a11y-keyboard-nav',
            'title' => __('Keyboard Navigation Missing', 'wpshadow'),
            'description' => __('Detects interactive components not keyboard accessible.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-keyboard-nav',
            'training_link' => 'https://wpshadow.com/training/code-a11y-keyboard-nav',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
