<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Autoloaded Options Bloat
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-autoload-bloat
 * Training: https://wpshadow.com/training/code-memory-autoload-bloat
 */
class Diagnostic_Code_CODE_MEMORY_AUTOLOAD_BLOAT extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-autoload-bloat',
            'title' => __('Autoloaded Options Bloat', 'wpshadow'),
            'description' => __('Detects autoloaded options exceeding size thresholds.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-autoload-bloat',
            'training_link' => 'https://wpshadow.com/training/code-memory-autoload-bloat',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}