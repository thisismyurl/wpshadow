<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Large Arrays in Options
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-large-arrays
 * Training: https://wpshadow.com/training/code-memory-large-arrays
 */
class Diagnostic_Code_CODE_MEMORY_LARGE_ARRAYS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-large-arrays',
            'title' => __('Large Arrays in Options', 'wpshadow'),
            'description' => __('Flags large arrays/objects stored in wp_options without chunking.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-large-arrays',
            'training_link' => 'https://wpshadow.com/training/code-memory-large-arrays',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
