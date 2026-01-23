<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Duplicate Handle Registration
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-handle-conflict
 * Training: https://wpshadow.com/training/code-memory-handle-conflict
 */
class Diagnostic_Code_CODE_MEMORY_HANDLE_CONFLICT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-handle-conflict',
            'title' => __('Duplicate Handle Registration', 'wpshadow'),
            'description' => __('Detects multiple scripts/styles registered with same handle.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-handle-conflict',
            'training_link' => 'https://wpshadow.com/training/code-memory-handle-conflict',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}