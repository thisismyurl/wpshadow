<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Library Version Conflicts
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-lib-duplication
 * Training: https://wpshadow.com/training/code-memory-lib-duplication
 */
class Diagnostic_Code_CODE_MEMORY_LIB_DUPLICATION {
    public static function check() {
        return [
            'id' => 'code-memory-lib-duplication',
            'title' => __('Library Version Conflicts', 'wpshadow'),
            'description' => __('Detects multiple versions of same library (jQuery, React, etc).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-lib-duplication',
            'training_link' => 'https://wpshadow.com/training/code-memory-lib-duplication',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

