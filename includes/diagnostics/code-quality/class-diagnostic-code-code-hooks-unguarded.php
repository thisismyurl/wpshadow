<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unguarded Hook Actions
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hooks-unguarded
 * Training: https://wpshadow.com/training/code-hooks-unguarded
 */
class Diagnostic_Code_CODE_HOOKS_UNGUARDED extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hooks-unguarded',
            'title' => __('Unguarded Hook Actions', 'wpshadow'),
            'description' => __('Detects actions/filters registered without capability checks.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hooks-unguarded',
            'training_link' => 'https://wpshadow.com/training/code-hooks-unguarded',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
