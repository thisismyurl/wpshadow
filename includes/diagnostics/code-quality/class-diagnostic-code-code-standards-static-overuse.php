<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Overuse of Statics
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-static-overuse
 * Training: https://wpshadow.com/training/code-standards-static-overuse
 */
class Diagnostic_Code_CODE_STANDARDS_STATIC_OVERUSE extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-static-overuse',
            'title' => __('Overuse of Statics', 'wpshadow'),
            'description' => __('Flags static state where instance properties sufficient.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-static-overuse',
            'training_link' => 'https://wpshadow.com/training/code-standards-static-overuse',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}