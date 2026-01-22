<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Deprecated Hooks/APIs
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-deprecated-hooks
 * Training: https://wpshadow.com/training/code-deprecated-hooks
 */
class Diagnostic_Code_CODE_DEPRECATED_HOOKS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-deprecated-hooks',
            'title' => __('Deprecated Hooks/APIs', 'wpshadow'),
            'description' => __('Flags use of hooks removed in recent WordPress versions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-deprecated-hooks',
            'training_link' => 'https://wpshadow.com/training/code-deprecated-hooks',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
