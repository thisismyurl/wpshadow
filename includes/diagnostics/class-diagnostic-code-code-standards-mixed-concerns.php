<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Mixed Concerns (MVC)
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-mixed-concerns
 * Training: https://wpshadow.com/training/code-standards-mixed-concerns
 */
class Diagnostic_Code_CODE_STANDARDS_MIXED_CONCERNS {
    public static function check() {
        return [
            'id' => 'code-standards-mixed-concerns',
            'title' => __('Mixed Concerns (MVC)', 'wpshadow'),
            'description' => __('Detects database logic mixed with HTML/rendering in same function.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-mixed-concerns',
            'training_link' => 'https://wpshadow.com/training/code-standards-mixed-concerns',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

