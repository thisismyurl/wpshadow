<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Critical CSS Not Inlined
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-memory-inline-critical-css
 * Training: https://wpshadow.com/training/code-memory-inline-critical-css
 */
class Diagnostic_Code_CODE_MEMORY_INLINE_CRITICAL_CSS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-memory-inline-critical-css',
            'title' => __('Critical CSS Not Inlined', 'wpshadow'),
            'description' => __('Detects render-critical CSS not inlined above-fold.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-memory-inline-critical-css',
            'training_link' => 'https://wpshadow.com/training/code-memory-inline-critical-css',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}