<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Library Shipped Multiple Times
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-hygiene-lib-duplicates
 * Training: https://wpshadow.com/training/code-hygiene-lib-duplicates
 */
class Diagnostic_Code_CODE_HYGIENE_LIB_DUPLICATES extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-hygiene-lib-duplicates',
            'title' => __('Library Shipped Multiple Times', 'wpshadow'),
            'description' => __('Flags React/Vue/jQuery shipped by multiple plugins.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-hygiene-lib-duplicates',
            'training_link' => 'https://wpshadow.com/training/code-hygiene-lib-duplicates',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}