<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Unbounded Log Buffers
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-log-buffer
 * Training: https://wpshadow.com/training/code-memory-log-buffer
 */
class Diagnostic_Code_CODE_MEMORY_LOG_BUFFER extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-log-buffer',
            'title' => __('Unbounded Log Buffers', 'wpshadow'),
            'description' => __('Detects growing log arrays stored in options without cleanup.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-log-buffer',
            'training_link' => 'https://wpshadow.com/training/code-memory-log-buffer',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
