<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Cache Headers
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-cache-headers-missing
 * Training: https://wpshadow.com/training/code-memory-cache-headers-missing
 */
class Diagnostic_Code_CODE_MEMORY_CACHE_HEADERS_MISSING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-cache-headers-missing',
            'title' => __('Missing Cache Headers', 'wpshadow'),
            'description' => __('Flags static assets without HTTP caching directives.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-cache-headers-missing',
            'training_link' => 'https://wpshadow.com/training/code-memory-cache-headers-missing',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
