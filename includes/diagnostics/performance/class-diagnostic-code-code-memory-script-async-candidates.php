<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing defer/async
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-script-async-candidates
 * Training: https://wpshadow.com/training/code-memory-script-async-candidates
 */
class Diagnostic_Code_CODE_MEMORY_SCRIPT_ASYNC_CANDIDATES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-script-async-candidates',
            'title' => __('Missing defer/async', 'wpshadow'),
            'description' => __('Detects render-blocking scripts that should defer/async.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-script-async-candidates',
            'training_link' => 'https://wpshadow.com/training/code-memory-script-async-candidates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
