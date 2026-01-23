<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Per-Request File Reads
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-file-read-per-request
 * Training: https://wpshadow.com/training/code-perf-file-read-per-request
 */
class Diagnostic_Code_CODE_PERF_FILE_READ_PER_REQUEST extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-perf-file-read-per-request',
            'title' => __('Per-Request File Reads', 'wpshadow'),
            'description' => __('Detects template/data files read on every request without cache.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-file-read-per-request',
            'training_link' => 'https://wpshadow.com/training/code-perf-file-read-per-request',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}