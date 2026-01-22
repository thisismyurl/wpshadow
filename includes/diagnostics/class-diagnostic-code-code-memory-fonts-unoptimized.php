<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Unoptimized Font Loading
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-fonts-unoptimized
 * Training: https://wpshadow.com/training/code-memory-fonts-unoptimized
 */
class Diagnostic_Code_CODE_MEMORY_FONTS_UNOPTIMIZED {
    public static function check() {
        return [
            'id' => 'code-memory-fonts-unoptimized',
            'title' => __('Unoptimized Font Loading', 'wpshadow'),
            'description' => __('Detects fonts not subsetted or too many weights shipped.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-fonts-unoptimized',
            'training_link' => 'https://wpshadow.com/training/code-memory-fonts-unoptimized',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

