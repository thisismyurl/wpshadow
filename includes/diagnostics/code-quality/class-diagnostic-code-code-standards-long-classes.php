<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Large Classes
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-long-classes
 * Training: https://wpshadow.com/training/code-standards-long-classes
 */
class Diagnostic_Code_CODE_STANDARDS_LONG_CLASSES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-long-classes',
            'title' => __('Large Classes', 'wpshadow'),
            'description' => __('Detects classes exceeding size thresholds without refactoring.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-long-classes',
            'training_link' => 'https://wpshadow.com/training/code-standards-long-classes',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}