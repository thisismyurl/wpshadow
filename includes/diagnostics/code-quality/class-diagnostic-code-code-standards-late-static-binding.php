<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Late Static Binding Misuse
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-late-static-binding
 * Training: https://wpshadow.com/training/code-standards-late-static-binding
 */
class Diagnostic_Code_CODE_STANDARDS_LATE_STATIC_BINDING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-late-static-binding',
            'title' => __('Late Static Binding Misuse', 'wpshadow'),
            'description' => __('Detects incorrect use of static:: vs self::.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-late-static-binding',
            'training_link' => 'https://wpshadow.com/training/code-standards-late-static-binding',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
