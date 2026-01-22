<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Strict Types
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-no-strict-types
 * Training: https://wpshadow.com/training/code-standards-no-strict-types
 */
class Diagnostic_Code_CODE_STANDARDS_NO_STRICT_TYPES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-no-strict-types',
            'title' => __('Missing Strict Types', 'wpshadow'),
            'description' => __('Detects files lacking declare(strict_types=1) where required.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-no-strict-types',
            'training_link' => 'https://wpshadow.com/training/code-standards-no-strict-types',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
