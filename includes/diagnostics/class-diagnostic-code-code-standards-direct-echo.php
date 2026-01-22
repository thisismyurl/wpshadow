<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Direct Echo in Logic
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-direct-echo
 * Training: https://wpshadow.com/training/code-standards-direct-echo
 */
class Diagnostic_Code_CODE_STANDARDS_DIRECT_ECHO {
    public static function check() {
        return [
            'id' => 'code-standards-direct-echo',
            'title' => __('Direct Echo in Logic', 'wpshadow'),
            'description' => __('Detects echo/print statements outside view/template layers.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-direct-echo',
            'training_link' => 'https://wpshadow.com/training/code-standards-direct-echo',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

