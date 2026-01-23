<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Tight Module Coupling
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-tight-coupling
 * Training: https://wpshadow.com/training/code-standards-tight-coupling
 */
class Diagnostic_Code_CODE_STANDARDS_TIGHT_COUPLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-tight-coupling',
            'title' => __('Tight Module Coupling', 'wpshadow'),
            'description' => __('Flags hard dependencies between plugins/themes without interfaces.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-tight-coupling',
            'training_link' => 'https://wpshadow.com/training/code-standards-tight-coupling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}