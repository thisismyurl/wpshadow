<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Magic Numbers/Strings
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-magic-numbers
 * Training: https://wpshadow.com/training/code-standards-magic-numbers
 */
class Diagnostic_Code_CODE_STANDARDS_MAGIC_NUMBERS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-magic-numbers',
            'title' => __('Magic Numbers/Strings', 'wpshadow'),
            'description' => __('Detects hardcoded values that should be named constants.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-magic-numbers',
            'training_link' => 'https://wpshadow.com/training/code-standards-magic-numbers',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
