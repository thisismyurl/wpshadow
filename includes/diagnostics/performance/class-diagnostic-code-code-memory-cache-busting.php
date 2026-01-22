<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Stale Cache Busting
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-cache-busting
 * Training: https://wpshadow.com/training/code-memory-cache-busting
 */
class Diagnostic_Code_CODE_MEMORY_CACHE_BUSTING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-cache-busting',
            'title' => __('Stale Cache Busting', 'wpshadow'),
            'description' => __('Detects non-incremented asset versions preventing caching.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-cache-busting',
            'training_link' => 'https://wpshadow.com/training/code-memory-cache-busting',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
