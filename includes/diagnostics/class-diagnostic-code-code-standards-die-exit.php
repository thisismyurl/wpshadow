<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Die/Exit in Plugins
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-die-exit
 * Training: https://wpshadow.com/training/code-standards-die-exit
 */
class Diagnostic_Code_CODE_STANDARDS_DIE_EXIT {
    public static function check() {
        return [
            'id' => 'code-standards-die-exit',
            'title' => __('Die/Exit in Plugins', 'wpshadow'),
            'description' => __('Flags die()/exit() calls in plugin code (non-fatal alternative needed).', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-die-exit',
            'training_link' => 'https://wpshadow.com/training/code-standards-die-exit',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

