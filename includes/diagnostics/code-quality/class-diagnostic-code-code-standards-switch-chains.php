<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Long Switch/If Chains
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-switch-chains
 * Training: https://wpshadow.com/training/code-standards-switch-chains
 */
class Diagnostic_Code_CODE_STANDARDS_SWITCH_CHAINS extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'code-standards-switch-chains',
            'title' => __('Long Switch/If Chains', 'wpshadow'),
            'description' => __('Flags switch/if chains that should use maps or polymorphism.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-switch-chains',
            'training_link' => 'https://wpshadow.com/training/code-standards-switch-chains',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }

}