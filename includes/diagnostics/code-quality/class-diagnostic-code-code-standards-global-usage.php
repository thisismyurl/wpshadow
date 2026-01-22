<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Global Variables
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-global-usage
 * Training: https://wpshadow.com/training/code-standards-global-usage
 */
class Diagnostic_Code_CODE_STANDARDS_GLOBAL_USAGE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-global-usage',
            'title' => __('Global Variables', 'wpshadow'),
            'description' => __('Detects use of global keyword or non-namespaced functions.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-global-usage',
            'training_link' => 'https://wpshadow.com/training/code-standards-global-usage',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
