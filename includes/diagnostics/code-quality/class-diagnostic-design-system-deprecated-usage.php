<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Deprecated Component Usage
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-deprecated-usage
 * Training: https://wpshadow.com/training/design-system-deprecated-usage
 */
class Diagnostic_Design_SYSTEM_DEPRECATED_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-system-deprecated-usage',
            'title' => __('Deprecated Component Usage', 'wpshadow'),
            'description' => __('Finds usage of deprecated design system components.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-system-deprecated-usage',
            'training_link' => 'https://wpshadow.com/training/design-system-deprecated-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
