<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Z-Index Conflicts
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-frontend-zindex-chaos
 * Training: https://wpshadow.com/training/code-frontend-zindex-chaos
 */
class Diagnostic_Code_CODE_FRONTEND_ZINDEX_CHAOS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-frontend-zindex-chaos',
            'title' => __('Z-Index Conflicts', 'wpshadow'),
            'description' => __('Detects overlapping/conflicting z-index causing hidden UI.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-frontend-zindex-chaos',
            'training_link' => 'https://wpshadow.com/training/code-frontend-zindex-chaos',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}
