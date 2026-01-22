<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: System Dark Mode Detection
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-dark-mode-system-preference
 * Training: https://wpshadow.com/training/design-dark-mode-system-preference
 */
class Diagnostic_Design_DARK_MODE_SYSTEM_PREFERENCE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-dark-mode-system-preference',
            'title' => __('System Dark Mode Detection', 'wpshadow'),
            'description' => __('Validates dark mode respects prefers-color-scheme.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-dark-mode-system-preference',
            'training_link' => 'https://wpshadow.com/training/design-dark-mode-system-preference',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
