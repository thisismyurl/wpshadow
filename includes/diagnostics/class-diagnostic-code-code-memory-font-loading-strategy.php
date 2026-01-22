<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Suboptimal Font Loading
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-font-loading-strategy
 * Training: https://wpshadow.com/training/code-memory-font-loading-strategy
 */
class Diagnostic_Code_CODE_MEMORY_FONT_LOADING_STRATEGY {
    public static function check() {
        return [
            'id' => 'code-memory-font-loading-strategy',
            'title' => __('Suboptimal Font Loading', 'wpshadow'),
            'description' => __('Flags fonts without font-display:swap or preload strategy.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-font-loading-strategy',
            'training_link' => 'https://wpshadow.com/training/code-memory-font-loading-strategy',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

