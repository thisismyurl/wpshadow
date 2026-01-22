<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Expensive Regex Patterns
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-perf-regex-hotspot
 * Training: https://wpshadow.com/training/code-perf-regex-hotspot
 */
class Diagnostic_Code_CODE_PERF_REGEX_HOTSPOT {
    public static function check() {
        return [
            'id' => 'code-perf-regex-hotspot',
            'title' => __('Expensive Regex Patterns', 'wpshadow'),
            'description' => __('Flags unoptimized regex in hooks/shortcodes on hot paths.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-perf-regex-hotspot',
            'training_link' => 'https://wpshadow.com/training/code-perf-regex-hotspot',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

